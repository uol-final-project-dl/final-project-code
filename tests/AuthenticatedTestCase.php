<?php

namespace Tests;

use App\Models\Project;
use App\Models\ProjectIdea;
use App\Models\Prototype;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

abstract class AuthenticatedTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->user);

        $this->project = Project::safeInstance(Project::factory()->create([
            'user_id' => $this->user->id,
        ]));

        $this->project_idea = ProjectIdea::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $this->prototype = Prototype::factory()->create([
            'user_id' => $this->user->id,
            'project_idea_id' => $this->project_idea->id
        ]);

        $mockNotifications = Mockery::mock('alias:App\Services\WebSocket\NotifyService');
        $mockNotifications->shouldReceive('reloadUserPage')->andReturn(null);
    }
}
