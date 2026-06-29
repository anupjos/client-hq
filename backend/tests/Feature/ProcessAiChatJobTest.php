<?php

namespace Tests\Feature;

use App\Enums\ChatMessageRole;
use App\Enums\ChatMessageStatus;
use App\Jobs\ProcessAiChatJob;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use App\Services\ClaudeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessAiChatJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_calls_anthropic_and_updates_placeholder(): void
    {
        Storage::fake('local');
        config()->set('services.anthropic.key', 'test-key');

        $client = User::factory()->client()->create();
        $project = Project::factory()->create([
            'client_id' => $client->id,
            'notes' => 'Brand colors are blue and amber.',
        ]);

        Storage::disk('local')->put("project_files/{$project->id}/brand.md", '# Brand: Blue #1F4FFF, Amber #FFC857');
        ProjectFile::create([
            'project_id' => $project->id,
            'uploaded_by' => $client->id,
            'original_name' => 'brand.md',
            'stored_path' => "project_files/{$project->id}/brand.md",
            'mime_type' => 'text/markdown',
            'size' => 100,
        ]);

        $userMsg = $project->messages()->create([
            'user_id' => $client->id,
            'role' => ChatMessageRole::User,
            'content' => 'What are the brand colors?',
            'status' => ChatMessageStatus::Completed,
        ]);

        $placeholder = $project->messages()->create([
            'user_id' => null,
            'role' => ChatMessageRole::Assistant,
            'content' => '',
            'status' => ChatMessageStatus::Pending,
        ]);

        Http::fake([
            '*/v1/messages' => Http::response([
                'content' => [['type' => 'text', 'text' => 'Blue (#1F4FFF) and Amber (#FFC857).']],
            ], 200),
        ]);

        (new ProcessAiChatJob($placeholder->id))->handle(app(ClaudeService::class));

        $placeholder->refresh();
        $this->assertSame(ChatMessageStatus::Completed, $placeholder->status);
        $this->assertStringContainsString('Blue', $placeholder->content);

        Http::assertSent(function ($request) use ($userMsg) {
            $body = json_decode($request->body(), true);

            return str_ends_with($request->url(), '/v1/messages')
                && $request->hasHeader('x-api-key', 'test-key')
                && str_contains($body['system'], 'brand.md')
                && $body['messages'][0]['content'] === $userMsg->content;
        });
    }

    public function test_job_marks_placeholder_failed_on_api_error(): void
    {
        config()->set('services.anthropic.key', 'test-key');

        $project = Project::factory()->create();
        $placeholder = $project->messages()->create([
            'user_id' => null,
            'role' => ChatMessageRole::Assistant,
            'content' => '',
            'status' => ChatMessageStatus::Pending,
        ]);

        Http::fake(['*/v1/messages' => Http::response(['error' => 'boom'], 500)]);

        (new ProcessAiChatJob($placeholder->id))->handle(app(ClaudeService::class));

        $placeholder->refresh();
        $this->assertSame(ChatMessageStatus::Failed, $placeholder->status);
        $this->assertNotEmpty($placeholder->error);
    }

    public function test_job_fails_cleanly_when_api_key_missing(): void
    {
        config()->set('services.anthropic.key', null);

        $project = Project::factory()->create();
        $placeholder = $project->messages()->create([
            'user_id' => null,
            'role' => ChatMessageRole::Assistant,
            'content' => '',
            'status' => ChatMessageStatus::Pending,
        ]);

        (new ProcessAiChatJob($placeholder->id))->handle(app(ClaudeService::class));

        $placeholder->refresh();
        $this->assertSame(ChatMessageStatus::Failed, $placeholder->status);
        $this->assertStringContainsString('ANTHROPIC_API_KEY', $placeholder->error);
    }
}
