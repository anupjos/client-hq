<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ClaudeService
{
    private const FILE_CHAR_LIMIT = 50_000;

    public function chat(Project $project, array $history): string
    {
        $apiKey = config('services.anthropic.key');
        if (empty($apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])
            ->timeout(60)
            ->post(config('services.anthropic.base_url').'/v1/messages', [
                'model' => config('services.anthropic.model'),
                'max_tokens' => config('services.anthropic.max_tokens'),
                'system' => $this->buildSystemPrompt($project),
                'messages' => $history,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Anthropic API error: '.$response->status().' '.$response->body());
        }

        $text = $response->json('content.0.text');
        if (! is_string($text) || $text === '') {
            throw new RuntimeException('Anthropic returned an empty response.');
        }

        return $text;
    }

    private function buildSystemPrompt(Project $project): string
    {
        $sections = [
            "You are an AI assistant helping a client understand their project. Answer questions using only the project context below. If the answer isn't in the context, say so honestly.",
            "PROJECT: {$project->name}",
        ];

        if (! empty($project->notes)) {
            $sections[] = "NOTES:\n{$project->notes}";
        }

        $fileBlocks = [];
        foreach ($project->files as $file) {
            if (! $this->isTextFile($file->original_name)) {
                continue;
            }

            $contents = Storage::disk('local')->get($file->stored_path);
            if ($contents === null) {
                continue;
            }

            $truncated = mb_substr($contents, 0, self::FILE_CHAR_LIMIT);
            $fileBlocks[] = "--- {$file->original_name} ---\n{$truncated}";
        }

        if (! empty($fileBlocks)) {
            $sections[] = "UPLOADED FILES:\n".implode("\n\n", $fileBlocks);
        }

        return implode("\n\n", $sections);
    }

    private function isTextFile(string $name): bool
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return in_array($ext, ['txt', 'md', 'markdown'], true);
    }
}
