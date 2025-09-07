<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CodeFile>
 */
class CodeFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'path' => $this->faker->filePath(),
            'content' => $this->faker->paragraphs(3, true),
            'project_id' => \App\Models\Project::factory(),
            'name' => $this->faker->word() . '.php',
            'type' => 'php',
            'summary' => $this->faker->sentence(10),
        ];
    }
}
