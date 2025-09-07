<?php

namespace Tests\Feature\Http\Controllers\Projects;

use App\Enums\ProjectStageEnum;
use App\Enums\StatusEnum;
use App\Jobs\Brainstorming\CreateIdeasFromProjectDocumentsJob;
use App\Jobs\Ideating\CreatePrototypeRequestsFromIdeasJob;
use App\Models\ProjectIdea;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\AuthenticatedTestCase;

class SingleProjectControllerTest extends AuthenticatedTestCase
{
    public function test_get_project(): void
    {
        $response = $this->get('/api/project/' . $this->project->id);
        $response->assertStatus(200);
        $response->assertSee($this->project->name);
    }

    public function test_upload_documents(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('sample.pdf', 100, 'application/pdf');

        $response = $this->post(
            '/api/project/' . $this->project->id . '/brainstorming/upload-documents',
            ['file' => $file],
            ['Content-Type' => 'multipart/form-data']
        );

        $response->assertStatus(200);
        $response->assertJsonFragment(['result' => 1]);
        $this->assertDatabaseCount('project_documents', 1);
    }

    public function test_update_stage(): void
    {
        $newStage = ProjectStageEnum::BRAINSTORMING->value;

        $response = $this->post(
            '/api/project/' . $this->project->id . '/update-stage',
            ['stage' => $newStage]
        );

        $response->assertStatus(200);
        $response->assertJsonFragment(['result' => 1]);
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'stage' => $newStage,
        ]);
    }

    public function test_update_status(): void
    {
        $this->project->update([
            'stage' => ProjectStageEnum::BRAINSTORMING->value,
            'status' => StatusEnum::READY->value,
        ]);

        $newStatus = StatusEnum::FAILED->value;

        $response = $this->post(
            '/api/project/' . $this->project->id . '/update-status',
            ['status' => $newStatus]
        );

        $response->assertStatus(200);
        $response->assertJsonFragment(['result' => 1]);
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'status' => $newStatus,
        ]);
    }

    public function test_update_status_brainstorming_queue(): void
    {
        $this->project->update([
            'stage' => ProjectStageEnum::BRAINSTORMING->value,
            'status' => StatusEnum::READY->value,
        ]);

        $newStatus = StatusEnum::QUEUED->value;

        Queue::fake();

        $response = $this->post(
            '/api/project/' . $this->project->id . '/update-status',
            ['status' => $newStatus]
        );

        Queue::assertPushed(CreateIdeasFromProjectDocumentsJob::class);

        $response->assertStatus(200);
        $response->assertJsonFragment(['result' => 1]);
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'status' => $newStatus,
        ]);
    }

    public function test_update_status_ideating_queue(): void
    {
        $this->project->update([
            'stage' => ProjectStageEnum::IDEATING->value,
            'status' => StatusEnum::READY->value,
        ]);

        $newStatus = StatusEnum::QUEUED->value;

        $projectIdea = ProjectIdea::factory()->create([
            'project_id' => $this->project->id,
            'status' => StatusEnum::REQUEST_DATA->value,
        ]);

        Queue::fake();

        $response = $this->post(
            '/api/project/' . $this->project->id . '/update-status',
            [
                'status' => $newStatus,
                'extra' => [
                    'selected_ideas' => [$projectIdea->id]
                ],
            ]
        );

        Queue::assertPushed(CreatePrototypeRequestsFromIdeasJob::class);

        $response->assertStatus(200);
        $response->assertJsonFragment(['result' => 1]);
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'status' => $newStatus,
        ]);
        $this->assertDatabaseHas('project_ideas', [
            'project_id' => $this->project->id,
            'id' => $projectIdea->id,
            'status' => StatusEnum::READY->value,
        ]);
    }
}
