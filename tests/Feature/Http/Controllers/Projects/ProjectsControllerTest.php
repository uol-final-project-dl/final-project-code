<?php

namespace Tests\Feature\Http\Controllers\Projects;

use App\Models\Project;
use Tests\AuthenticatedTestCase;

class ProjectsControllerTest extends AuthenticatedTestCase
{
    public function test_get_projects(): void
    {
        $project = Project::safeInstance(Project::factory()->create([
            'user_id' => $this->user->id,
        ]));

        $response = $this->get('/api/projects');
        $response->assertStatus(200);
        $response->assertSee($project->name);
    }

    public function test_project_create(): void
    {
        $postData = [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
        ];

        $response = $this->post(
            '/api/project/create',
            $postData
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', $postData);
    }
}
