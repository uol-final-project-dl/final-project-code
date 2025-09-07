<?php

namespace Database\Factories;

use App\Enums\PrototypeTypeEnum;
use App\Enums\StatusEnum;
use App\Models\ProjectIdea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prototype>
 */
class PrototypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(PrototypeTypeEnum::getValues()),
            'user_id' => User::factory(),
            'project_idea_id' => ProjectIdea::factory(),
            'uuid' => $this->faker->uuid(),
            'title' => $this->faker->sentence(6),
            'description' => $this->faker->paragraph(),
            'status' => StatusEnum::REQUEST_DATA->value,
            'bundle' => null,
            'log' => null,
        ];
    }
}
