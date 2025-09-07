<?php

namespace Tests\Feature\Jobs\Brainstorming;

use App\Enums\ProjectStageEnum;
use App\Enums\StatusEnum;
use App\Jobs\Brainstorming\CreateIdeasFromProjectDocumentsJob;
use App\Models\CodeFile;
use JsonException;
use Mockery;
use Tests\AuthenticatedTestCase;

class CreateIdeasFromProjectDocumentsJobTest extends AuthenticatedTestCase
{
    public function test_handle_prototype(): void
    {
        $mockIdeas = [
            [
                'title' => fake()->sentence(3),
                'description' => fake()->paragraph,
                'ranking' => 1,
            ],
            [
                'title' => fake()->sentence(3),
                'description' => fake()->paragraph,
                'ranking' => 2,
            ],
        ];

        $mock = Mockery::mock('alias:App\Services\IdeaGeneration\IdeaGenerationService');
        $mock->shouldReceive('generateIdeas')->once()->andReturn([json_encode($mockIdeas, JSON_THROW_ON_ERROR), []]);

        CreateIdeasFromProjectDocumentsJob::dispatch($this->project);

        $this->assertDatabaseHas('project_ideas', [
            'project_id' => $this->project->id,
            'title' => $mockIdeas[0]['title'],
            'description' => $mockIdeas[0]['description'],
        ]);
        $this->assertDatabaseHas('project_ideas', [
            'project_id' => $this->project->id,
            'title' => $mockIdeas[1]['title'],
            'description' => $mockIdeas[1]['description'],
        ]);
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'stage' => ProjectStageEnum::IDEATING->value,
            'status' => StatusEnum::REQUEST_DATA->value
        ]);
    }

    /**
     * @throws JsonException
     */
    public function test_handle_pull_request(): void
    {
        $mockIdeas = [
            [
                'title' => fake()->sentence(3),
                'description' => fake()->paragraph,
                'ranking' => 1,
            ],
            [
                'title' => fake()->sentence(3),
                'description' => fake()->paragraph,
                'ranking' => 2,
            ],
        ];

        $this->project->update([
            'github_repository_id' => 123456
        ]);

        $mock = Mockery::mock('alias:App\Services\IdeaGeneration\IdeaGenerationFromRepoService');
        $mock->shouldReceive('generateIdeas')->once()->andReturn([json_encode($mockIdeas, JSON_THROW_ON_ERROR), []]);

        CodeFile::factory()->create([
            'project_id' => $this->project->id,
            'path' => 'src',
            'name' => 'example.php',
            'content' => '<?php echo "Hello, world!"; ?>'
        ]);

        CreateIdeasFromProjectDocumentsJob::dispatch($this->project);

        $this->assertDatabaseHas('project_ideas', [
            'project_id' => $this->project->id,
            'title' => $mockIdeas[0]['title'],
            'description' => $mockIdeas[0]['description'],
        ]);
        $this->assertDatabaseHas('project_ideas', [
            'project_id' => $this->project->id,
            'title' => $mockIdeas[1]['title'],
            'description' => $mockIdeas[1]['description'],
        ]);
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'stage' => ProjectStageEnum::IDEATING->value,
            'status' => StatusEnum::REQUEST_DATA->value
        ]);
    }

    public function test_handle_unhappy_path(): void
    {
        $mock = Mockery::mock('alias:App\Services\IdeaGeneration\IdeaGenerationService');
        $mock->shouldReceive('generateIdeas')->once()->andReturn(['[{asdfasdfasfasdfadsf,', []]);

        CreateIdeasFromProjectDocumentsJob::dispatch($this->project);

        $this->assertDatabaseCount('project_ideas', 1);
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'stage' => ProjectStageEnum::BRAINSTORMING->value,
            'status' => StatusEnum::REQUEST_DATA->value
        ]);
    }
}
