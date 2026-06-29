<?php

namespace Tests\Feature;

use App\Enums\ChatMessageRole;
use App\Enums\ChatMessageStatus;
use App\Jobs\ProcessAiChatJob;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChatMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_post_a_message_to_their_project(): void
    {
        Queue::fake();

        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        Sanctum::actingAs($client);

        $response = $this->postJson("/api/projects/{$project->id}/messages", [
            'content' => 'What is the timeline?',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user_message.content', 'What is the timeline?')
            ->assertJsonPath('user_message.role', 'user')
            ->assertJsonPath('assistant_placeholder.role', 'assistant')
            ->assertJsonPath('assistant_placeholder.status', 'pending');

        $this->assertDatabaseCount('chat_messages', 2);

        Queue::assertPushed(ProcessAiChatJob::class, function ($job) use ($response) {
            return $job->placeholderMessageId === $response->json('assistant_placeholder.id');
        });
    }

    public function test_client_cannot_post_to_other_clients_project(): void
    {
        Queue::fake();

        $client = User::factory()->client()->create();
        $other = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $other->id]);

        Sanctum::actingAs($client);

        $this->postJson("/api/projects/{$project->id}/messages", [
            'content' => 'hello',
        ])->assertForbidden();

        Queue::assertNothingPushed();
    }

    public function test_listing_messages_returns_them_in_order(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $project->messages()->createMany([
            ['user_id' => $client->id, 'role' => ChatMessageRole::User, 'content' => 'q1', 'status' => ChatMessageStatus::Completed],
            ['user_id' => null, 'role' => ChatMessageRole::Assistant, 'content' => 'a1', 'status' => ChatMessageStatus::Completed],
            ['user_id' => $client->id, 'role' => ChatMessageRole::User, 'content' => 'q2', 'status' => ChatMessageStatus::Completed],
        ]);

        Sanctum::actingAs($client);

        $response = $this->getJson("/api/projects/{$project->id}/messages");

        $response->assertOk()->assertJsonCount(3);
        $this->assertSame(['q1', 'a1', 'q2'], array_column($response->json(), 'content'));
    }

    public function test_content_is_required(): void
    {
        Queue::fake();
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        Sanctum::actingAs($client);

        $this->postJson("/api/projects/{$project->id}/messages", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('content');
    }
}
