<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_projects(): void
    {
        $admin = User::factory()->admin()->create();
        Project::factory()->count(3)->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_client_only_sees_their_own_projects(): void
    {
        $client = User::factory()->client()->create();
        $other = User::factory()->client()->create();
        $admin = User::factory()->admin()->create();

        Project::factory()->count(2)->create(['client_id' => $client->id, 'created_by' => $admin->id]);
        Project::factory()->count(3)->create(['client_id' => $other->id, 'created_by' => $admin->id]);

        Sanctum::actingAs($client);

        $this->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_admin_can_create_a_project(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();

        Sanctum::actingAs($admin);

        $this->postJson('/api/projects', [
            'client_id' => $client->id,
            'name' => 'New Site Build',
            'notes' => 'Kickoff next week.',
            'status' => 'active',
        ])->assertCreated()
            ->assertJsonPath('name', 'New Site Build')
            ->assertJsonPath('client_id', $client->id)
            ->assertJsonPath('created_by', $admin->id);

        $this->assertDatabaseHas('projects', ['name' => 'New Site Build']);
    }

    public function test_client_cannot_create_a_project(): void
    {
        $client = User::factory()->client()->create();
        $another = User::factory()->client()->create();

        Sanctum::actingAs($client);

        $this->postJson('/api/projects', [
            'client_id' => $another->id,
            'name' => 'Not Allowed',
        ])->assertForbidden();
    }

    public function test_create_validates_client_id_must_belong_to_a_client(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->postJson('/api/projects', [
            'client_id' => $otherAdmin->id,
            'name' => 'Misassigned',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('client_id');
    }

    public function test_client_cannot_view_other_clients_project(): void
    {
        $client = User::factory()->client()->create();
        $other = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $other->id]);

        Sanctum::actingAs($client);

        $this->getJson("/api/projects/{$project->id}")->assertForbidden();
    }

    public function test_client_can_view_their_own_project(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        Sanctum::actingAs($client);

        $this->getJson("/api/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('id', $project->id);
    }

    public function test_admin_can_update_project(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        Sanctum::actingAs($admin);

        $this->patchJson("/api/projects/{$project->id}", [
            'status' => 'completed',
        ])->assertOk()
            ->assertJsonPath('status', 'completed');
    }

    public function test_client_cannot_update_project(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        Sanctum::actingAs($client);

        $this->patchJson("/api/projects/{$project->id}", [
            'status' => 'completed',
        ])->assertForbidden();
    }
}
