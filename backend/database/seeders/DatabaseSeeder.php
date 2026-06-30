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

        $demoClient = User::factory()->client()->create([
            'name' => 'Demo Client',
            'email' => 'client@demo.test',
            'password' => Hash::make('password'),
        ]);

        $sarah = User::factory()->client()->create([
            'name' => 'Sarah Chen',
            'email' => 'sarah@acmeco.test',
            'password' => Hash::make('password'),
        ]);

        $marco = User::factory()->client()->create([
            'name' => 'Marco Diaz',
            'email' => 'marco@quokkahealth.test',
            'password' => Hash::make('password'),
        ]);

        $jenna = User::factory()->client()->create([
            'name' => 'Jenna Park',
            'email' => 'jenna@riversidecoop.test',
            'password' => Hash::make('password'),
        ]);

        // --- Demo Client's projects (the test login sees these) ---

        $acme = Project::create([
            'client_id' => $demoClient->id,
            'created_by' => $admin->id,
            'name' => 'Acme Co. Website Redesign',
            'notes' => "Goals:\n- Modernize visual design\n- Reduce checkout drop-off\n- Mobile-first layout\n\nTimeline: 8 weeks, launching in early September.\n\nKey stakeholder: Sarah (Head of Marketing) approves all designs.",
            'status' => ProjectStatus::Active,
        ]);

        $this->storeDemoFile(
            project: $acme,
            uploader: $admin,
            name: 'project-brief.md',
            content: "# Acme Co. Website Redesign – Brief\n\n## Background\nAcme has not updated its website since 2018. Conversion is down 18% YoY on mobile.\n\n## Scope\n- New homepage + product pages\n- Refreshed checkout flow (3 steps -> 1 step)\n- Updated brand palette (see brand-guide.txt)\n\n## Out of scope\n- Backend / inventory system\n- Email marketing redesign\n",
        );

        $this->storeDemoFile(
            project: $acme,
            uploader: $admin,
            name: 'brand-guide.txt',
            content: "Acme Co. Brand Guide\n=====================\nPrimary color: #1F4FFF (Acme Blue)\nSecondary color: #FFC857 (Acme Amber)\nNeutrals: #0B1220 (Ink), #F5F7FB (Paper)\nHeadings: Inter, weight 700\nBody: Inter, weight 400-500\nVoice: confident, plain, no jargon\n",
        );

        ChatMessage::create([
            'project_id' => $acme->id,
            'user_id' => $demoClient->id,
            'role' => ChatMessageRole::User,
            'content' => 'What colors are we using for the redesign?',
            'status' => ChatMessageStatus::Completed,
        ]);

        ChatMessage::create([
            'project_id' => $acme->id,
            'user_id' => null,
            'role' => ChatMessageRole::Assistant,
            'content' => "Based on your brand guide, the redesign uses:\n- Primary: Acme Blue (#1F4FFF)\n- Secondary: Acme Amber (#FFC857)\n- Neutrals: Ink (#0B1220) and Paper (#F5F7FB)\n\nHeadings use Inter at weight 700, body copy at 400-500.",
            'status' => ChatMessageStatus::Completed,
        ]);

        $quarterly = Project::create([
            'client_id' => $demoClient->id,
            'created_by' => $admin->id,
            'name' => 'Quarterly Strategy Review',
            'notes' => "Pause until Q3 budget is approved.\n\nDeliverables planned:\n- Competitor landscape deck\n- Customer interview synthesis\n- 12-month roadmap",
            'status' => ProjectStatus::Paused,
        ]);

        $this->storeDemoFile(
            project: $quarterly,
            uploader: $admin,
            name: 'q2-summary.md',
            content: "# Q2 Summary\n\n## Wins\n- Onboarded 4 enterprise clients\n- Reduced support response time from 14h to 5h\n\n## Misses\n- Mobile NPS still under target\n- Blog cadence slipped (2 posts vs 6 planned)\n\n## Next quarter focus\n- Mobile app v2\n- Content engine\n",
        );

        Project::create([
            'client_id' => $demoClient->id,
            'created_by' => $admin->id,
            'name' => 'Mobile App Onboarding Flow',
            'notes' => "Shipped in May. Final retro notes attached.\n\nMetric: time-to-first-value dropped from 4m12s to 1m48s. New activation rate up 22%.",
            'status' => ProjectStatus::Completed,
        ]);

        // --- Other clients' projects (admin sees these too) ---

        $brand = Project::create([
            'client_id' => $sarah->id,
            'created_by' => $admin->id,
            'name' => 'Acme Co. Brand Refresh 2026',
            'notes' => "Refresh the wordmark and supporting illustrations. Keep the existing Acme Blue.\n\nMilestones:\n- Mood boards: week 2\n- Wordmark explorations: week 4\n- Final + guidelines: week 6",
            'status' => ProjectStatus::Active,
        ]);

        $this->storeDemoFile(
            project: $brand,
            uploader: $admin,
            name: 'creative-brief.md',
            content: "# Brand Refresh 2026 – Creative brief\n\n## Audience\nMid-market SaaS buyers, 30-50, decision makers.\n\n## Brand attributes\n- Confident, not loud\n- Plain-spoken\n- Trusted\n\n## Avoid\n- Generic tech gradients\n- Cluttered illustrations\n",
        );

        $portal = Project::create([
            'client_id' => $marco->id,
            'created_by' => $admin->id,
            'name' => 'Quokka Health Patient Portal',
            'notes' => "HIPAA-compliant patient portal. Phase 1 covers scheduling + secure messaging.\n\nGo-live: October.",
            'status' => ProjectStatus::Active,
        ]);

        $this->storeDemoFile(
            project: $portal,
            uploader: $admin,
            name: 'requirements.md',
            content: "# Patient Portal – Phase 1\n\n## Must have\n- Appointment booking with provider availability\n- Secure inbox (TLS, encrypted at rest)\n- Document uploads (lab results)\n\n## Nice to have\n- Telehealth video shim\n- Calendar invite sync\n",
        );

        Project::create([
            'client_id' => $marco->id,
            'created_by' => $admin->id,
            'name' => 'Compliance Audit Report',
            'notes' => 'Annual HIPAA audit. Cleared on July 14 with two minor remediation items, both addressed by August 2.',
            'status' => ProjectStatus::Completed,
        ]);

        Project::create([
            'client_id' => $jenna->id,
            'created_by' => $admin->id,
            'name' => 'Riverside Co-op Member Newsletter',
            'notes' => 'Monthly newsletter redesign + content calendar. Looking to lift open rate from 22% to 35% by Q4.',
            'status' => ProjectStatus::Active,
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
