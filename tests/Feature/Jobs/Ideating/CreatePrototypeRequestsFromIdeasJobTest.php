<?php

namespace Tests\Feature\Jobs\Ideating;

use App\Enums\ProjectStageEnum;
use App\Enums\StatusEnum;
use App\Jobs\Ideating\CreatePrototypeRequestsFromIdeasJob;
use App\Jobs\Prototypes\GeneratePrototype;
use App\Jobs\Prototypes\GeneratePullRequest;
use App\Models\ProjectIdea;
use Illuminate\Support\Facades\Bus;
use Tests\AuthenticatedTestCase;

class CreatePrototypeRequestsFromIdeasJobTest extends AuthenticatedTestCase
{
    public function test_handle_prototype(): void
    {
        $newProjectIdea = ProjectIdea::factory()->create([
            'project_id' => $this->project->id,
            'status' => StatusEnum::READY->value,
        ]);

        Bus::fake([GeneratePrototype::class]);
        CreatePrototypeRequestsFromIdeasJob::dispatch($this->project);
        Bus::assertDispatched(GeneratePrototype::class);

        $this->assertDatabaseHas('prototypes', [
            'user_id' => $this->project->user_id,
            'project_idea_id' => $newProjectIdea->id,
            'title' => $newProjectIdea->title,
            'description' => $newProjectIdea->description,
            'status' => StatusEnum::QUEUED->value,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'stage' => ProjectStageEnum::PROTOTYPING->value,
            'status' => StatusEnum::QUEUED->value
        ]);
    }

    public function test_handle_pull_request(): void
    {
        $newProjectIdea = ProjectIdea::factory()->create([
            'project_id' => $this->project->id,
            'status' => StatusEnum::READY->value,
        ]);

        $this->project->update([
            'github_repository_id' => 123456,
        ]);

        Bus::fake([GeneratePullRequest::class]);
        CreatePrototypeRequestsFromIdeasJob::dispatch($this->project);
        Bus::assertDispatched(GeneratePullRequest::class);

        $this->assertDatabaseHas('prototypes', [
            'user_id' => $this->project->user_id,
            'project_idea_id' => $newProjectIdea->id,
            'title' => $newProjectIdea->title,
            'description' => $newProjectIdea->description,
            'status' => StatusEnum::QUEUED->value,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'stage' => ProjectStageEnum::PROTOTYPING->value,
            'status' => StatusEnum::QUEUED->value
        ]);
    }
}
