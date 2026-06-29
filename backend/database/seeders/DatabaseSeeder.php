<?php

namespace Database\Seeders;

use App\Enums\ChatMessageRole;
use App\Enums\ChatMessageStatus;
use App\Enums\ProjectStatus;
use App\Models\ChatMessage;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.test',
            'password' => Hash::make('password'),
        ]);

        $client = User::factory()->client()->create([
            'name' => 'Demo Client',
            'email' => 'client@demo.test',
            'password' => Hash::make('password'),
        ]);

        $project = Project::create([
            'client_id' => $client->id,
            'created_by' => $admin->id,
            'name' => 'Acme Co. Website Redesign',
            'notes' => "Goals:\n- Modernize visual design\n- Reduce checkout drop-off\n- Mobile-first layout\n\nTimeline: 8 weeks, launching in early September.\n\nKey stakeholder: Sarah (Head of Marketing) approves all designs.",
            'status' => ProjectStatus::Active,
        ]);

        $this->storeDemoFile(
            project: $project,
            uploader: $admin,
            name: 'project-brief.md',
            content: "# Acme Co. Website Redesign – Brief\n\n## Background\nAcme has not updated its website since 2018. Conversion is down 18% YoY on mobile.\n\n## Scope\n- New homepage + product pages\n- Refreshed checkout flow (3 steps -> 1 step)\n- Updated brand palette (see brand-guide.txt)\n\n## Out of scope\n- Backend / inventory system\n- Email marketing redesign\n",
        );

        $this->storeDemoFile(
            project: $project,
            uploader: $admin,
            name: 'brand-guide.txt',
            content: "Acme Co. Brand Guide\n=====================\nPrimary color: #1F4FFF (Acme Blue)\nSecondary color: #FFC857 (Acme Amber)\nNeutrals: #0B1220 (Ink), #F5F7FB (Paper)\nHeadings: Inter, weight 700\nBody: Inter, weight 400-500\nVoice: confident, plain, no jargon\n",
        );

        ChatMessage::create([
            'project_id' => $project->id,
            'user_id' => $client->id,
            'role' => ChatMessageRole::User,
            'content' => 'What colors are we using for the redesign?',
            'status' => ChatMessageStatus::Completed,
        ]);

        ChatMessage::create([
            'project_id' => $project->id,
            'user_id' => null,
            'role' => ChatMessageRole::Assistant,
            'content' => "Based on your brand guide, the redesign uses:\n- Primary: Acme Blue (#1F4FFF)\n- Secondary: Acme Amber (#FFC857)\n- Neutrals: Ink (#0B1220) and Paper (#F5F7FB)\n\nHeadings use Inter at weight 700, body copy at 400-500.",
            'status' => ChatMessageStatus::Completed,
        ]);
    }

    private function storeDemoFile(Project $project, User $uploader, string $name, string $content): void
    {
        $path = "project_files/{$project->id}/".str()->uuid()->toString().'-'.$name;
        Storage::disk('local')->put($path, $content);

        ProjectFile::create([
            'project_id' => $project->id,
            'uploaded_by' => $uploader->id,
            'original_name' => $name,
            'stored_path' => $path,
            'mime_type' => str_ends_with($name, '.md') ? 'text/markdown' : 'text/plain',
            'size' => strlen($content),
        ]);
    }
}
