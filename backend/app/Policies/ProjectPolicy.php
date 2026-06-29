<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isClient();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->client_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function uploadFile(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->client_id === $user->id;
    }

    public function chat(User $user, Project $project): bool
    {
        return $user->isAdmin() || $project->client_id === $user->id;
    }
}
