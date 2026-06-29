<?php

namespace App\Jobs;

use App\Enums\ChatMessageStatus;
use App\Models\ChatMessage;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessAiChatJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $placeholderMessageId) {}

    public function handle(ClaudeService $claude): void
    {
        $placeholder = ChatMessage::with('project.files')->find($this->placeholderMessageId);
        if (! $placeholder || $placeholder->status !== ChatMessageStatus::Pending) {
            return;
        }

        $history = $placeholder->project->messages()
            ->where('id', '<', $placeholder->id)
            ->where('status', ChatMessageStatus::Completed->value)
            ->orderBy('id')
            ->get(['role', 'content'])
            ->map(fn (ChatMessage $m) => [
                'role' => $m->role->value,
                'content' => $m->content,
            ])
            ->all();

        try {
            $reply = $claude->chat($placeholder->project, $history);

            $placeholder->update([
                'content' => $reply,
                'status' => ChatMessageStatus::Completed,
            ]);
        } catch (Throwable $e) {
            $placeholder->update([
                'status' => ChatMessageStatus::Failed,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $exception): void
    {
        ChatMessage::where('id', $this->placeholderMessageId)
            ->where('status', ChatMessageStatus::Pending->value)
            ->update([
                'status' => ChatMessageStatus::Failed->value,
                'error' => $exception->getMessage(),
            ]);
    }
}
