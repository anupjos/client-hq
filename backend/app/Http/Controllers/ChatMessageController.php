<?php

namespace App\Http\Controllers;

use App\Enums\ChatMessageRole;
use App\Enums\ChatMessageStatus;
use App\Http\Requests\StoreChatMessageRequest;
use App\Jobs\ProcessAiChatJob;
use App\Models\ChatMessage;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ChatMessageController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('chat', $project);

        return response()->json($project->messages()->get());
    }

    public function store(StoreChatMessageRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('chat', $project);

        [$userMessage, $placeholder] = DB::transaction(function () use ($request, $project) {
            $user = ChatMessage::create([
                'project_id' => $project->id,
                'user_id' => $request->user()->id,
                'role' => ChatMessageRole::User,
                'content' => $request->string('content')->toString(),
                'status' => ChatMessageStatus::Completed,
            ]);

            $placeholder = ChatMessage::create([
                'project_id' => $project->id,
                'user_id' => null,
                'role' => ChatMessageRole::Assistant,
                'content' => '',
                'status' => ChatMessageStatus::Pending,
            ]);

            return [$user, $placeholder];
        });

        ProcessAiChatJob::dispatch($placeholder->id);

        return response()->json([
            'user_message' => $userMessage,
            'assistant_placeholder' => $placeholder,
        ], 201);
    }
}
