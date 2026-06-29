<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => User::factory()->client(),
            'created_by' => User::factory()->admin(),
            'name' => fake()->company().' Website Redesign',
            'notes' => fake()->paragraph(),
            'status' => ProjectStatus::Active,
        ];
    }
}
