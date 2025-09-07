<?php

namespace Database\Factories;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectDocument>
 */
class ProjectDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => \App\Models\Project::factory(),
            'filename' => $this->faker->word() . '.txt',
            'type' => $this->faker->randomElement(['text', 'pdf', 'docx']),
            'status' => $this->faker->randomElement(StatusEnum::getValues()),
            'content' => $this->faker->paragraphs(3, true),
            'error_message' => null,
        ];
    }
}
