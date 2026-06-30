<?php

namespace Tests\Feature;

use App\Enums\ChatMessageRole;
use App\Enums\ChatMessageStatus;
use App\Models\ChatMessage;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_activity_across_all_projects(): void
    {
        $admin = User::factory()->admin()->create();
        $clientA = User::factory()->client()->create();
        $clientB = User::factory()->client()->create();

        Project::factory()->create(['client_id' => $clientA->id, 'created_by' => $admin->id]);
        Project::factory()->create(['client_id' => $clientB->id, 'created_by' => $admin->id]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/activity');

        $response->assertOk()->assertJsonStructure([
            ['type', 'project', 'actor', 'summary', 'at'],
        ]);
        $this->assertGreaterThanOrEqual(2, count($response->json()));
    }

    public function test_client_only_sees_activity_from_their_own_projects(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();
        $other = User::factory()->client()->create();

        $mine = Project::factory()->create(['client_id' => $client->id, 'created_by' => $admin->id, 'name' => 'Mine']);
        $theirs = Project::factory()->create(['client_id' => $other->id, 'created_by' => $admin->id, 'name' => 'Theirs']);

        ChatMessage::create([
            'project_id' => $theirs->id,
            'user_id' => $other->id,
            'role' => ChatMessageRole::User,
            'content' => 'hidden from me',
            'status' => ChatMessageStatus::Completed,
        ]);

        ProjectFile::create([
            'project_id' => $mine->id,
            'uploaded_by' => $admin->id,
            'original_name' => 'mine.txt',
            'stored_path' => 'x',
            'mime_type' => 'text/plain',
            'size' => 1,
        ]);

        Sanctum::actingAs($client);

        $response = $this->getJson('/api/activity');

        $response->assertOk();
        $names = collect($response->json())->pluck('project.name')->unique()->values()->all();

        $this->assertContains('Mine', $names);
        $this->assertNotContains('Theirs', $names);
    }
}
