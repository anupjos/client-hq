<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_upload_to_their_project(): void
    {
        Storage::fake('local');
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        Sanctum::actingAs($client);

        $response = $this->postJson("/api/projects/{$project->id}/files", [
            'file' => UploadedFile::fake()->createWithContent('brief.txt', 'hello'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('original_name', 'brief.txt')
            ->assertJsonPath('project_id', $project->id);

        $this->assertDatabaseCount('project_files', 1);
        Storage::disk('local')->assertExists($response->json('stored_path'));
    }

    public function test_client_cannot_upload_to_other_clients_project(): void
    {
        Storage::fake('local');
        $client = User::factory()->client()->create();
        $other = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $other->id]);

        Sanctum::actingAs($client);

        $this->postJson("/api/projects/{$project->id}/files", [
            'file' => UploadedFile::fake()->create('brief.txt', 1),
        ])->assertForbidden();
    }

    public function test_client_can_download_their_file(): void
    {
        Storage::fake('local');
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $path = "project_files/{$project->id}/brief.txt";
        Storage::disk('local')->put($path, 'hello world');

        $file = ProjectFile::create([
            'project_id' => $project->id,
            'uploaded_by' => $client->id,
            'original_name' => 'brief.txt',
            'stored_path' => $path,
            'mime_type' => 'text/plain',
            'size' => 11,
        ]);

        Sanctum::actingAs($client);

        $this->get("/api/projects/{$project->id}/files/{$file->id}")
            ->assertOk()
            ->assertDownload('brief.txt');
    }

    public function test_admin_can_delete_a_file(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $path = "project_files/{$project->id}/x.txt";
        Storage::disk('local')->put($path, 'x');
        $file = ProjectFile::create([
            'project_id' => $project->id,
            'uploaded_by' => $admin->id,
            'original_name' => 'x.txt',
            'stored_path' => $path,
            'mime_type' => 'text/plain',
            'size' => 1,
        ]);

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/projects/{$project->id}/files/{$file->id}")
            ->assertNoContent();

        $this->assertDatabaseCount('project_files', 0);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_client_cannot_delete_files(): void
    {
        Storage::fake('local');
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $path = "project_files/{$project->id}/x.txt";
        Storage::disk('local')->put($path, 'x');
        $file = ProjectFile::create([
            'project_id' => $project->id,
            'uploaded_by' => $client->id,
            'original_name' => 'x.txt',
            'stored_path' => $path,
            'mime_type' => 'text/plain',
            'size' => 1,
        ]);

        Sanctum::actingAs($client);

        $this->deleteJson("/api/projects/{$project->id}/files/{$file->id}")
            ->assertForbidden();
    }
}
