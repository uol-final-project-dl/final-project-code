<?php

namespace Database\Factories;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectIdea>
 */
class ProjectIdeaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'ranking' => $this->faker->numberBetween(1, 10),
            'status' => StatusEnum::REQUEST_DATA->value,
            'project_id' => \App\Models\Project::factory()
        ];
    }
}
