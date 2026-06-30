<?php

namespace App\Http\Controllers;

use App\Enums\ChatMessageRole;
use App\Models\ChatMessage;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = 15;

        $projectIds = Project::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('client_id', $user->id))
            ->pluck('id');

        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->with(['client:id,name', 'creator:id,name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Project $p) => [
                'type' => 'project_created',
                'project' => ['id' => $p->id, 'name' => $p->name],
                'actor' => $p->creator?->name ?? 'System',
                'summary' => "created project for {$p->client?->name}",
                'at' => $p->created_at?->toIso8601String(),
            ]);

        $files = ProjectFile::query()
            ->whereIn('project_id', $projectIds)
            ->with(['project:id,name', 'uploader:id,name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ProjectFile $f) => [
                'type' => 'file_uploaded',
                'project' => ['id' => $f->project?->id, 'name' => $f->project?->name],
                'actor' => $f->uploader?->name ?? 'System',
                'summary' => "uploaded {$f->original_name}",
                'at' => $f->created_at?->toIso8601String(),
            ]);

        $messages = ChatMessage::query()
            ->whereIn('project_id', $projectIds)
            ->with(['project:id,name', 'user:id,name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (ChatMessage $m) {
                $isUser = $m->role === ChatMessageRole::User;
                $snippet = mb_strimwidth($m->content ?? '', 0, 80, '…');

                return [
                    'type' => $isUser ? 'message_sent' : 'assistant_replied',
                    'project' => ['id' => $m->project?->id, 'name' => $m->project?->name],
                    'actor' => $isUser ? ($m->user?->name ?? 'Client') : 'Assistant',
                    'summary' => $isUser ? "asked: \"{$snippet}\"" : "replied: \"{$snippet}\"",
                    'at' => $m->created_at?->toIso8601String(),
                ];
            });

        $items = $projects->concat($files)->concat($messages)
            ->sortByDesc('at')
            ->values()
            ->take($limit);

        return response()->json($items);
    }
}
