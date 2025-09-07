<?php

namespace Tests\Feature\Jobs\Brainstorming;

use App\Enums\StatusEnum;
use App\Jobs\Brainstorming\ProcessProjectDocumentJob;
use App\Jobs\Brainstorming\ProcessProjectDocumentsJob;
use App\Models\ProjectDocument;
use Illuminate\Support\Facades\Bus;
use Tests\AuthenticatedTestCase;

class ProcessProjectDocumentsJobTest extends AuthenticatedTestCase
{
    public function test_handle(): void
    {
        ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'filename' => 'example.txt',
            'type' => 'text/plain',
            'status' => StatusEnum::QUEUED->value,
            'content' => null,
        ]);

        Bus::fake([ProcessProjectDocumentJob::class]);
        ProcessProjectDocumentsJob::dispatch($this->project);
        Bus::assertDispatched(ProcessProjectDocumentJob::class);
    }
}
