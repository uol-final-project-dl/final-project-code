<?php

namespace App\Jobs\Brainstorming;

use App\Enums\StatusEnum;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProjectDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Project $project;

    public function __construct(
        Project $project,
    )
    {
        $this->project = $project;
    }

    public function handle(): void
    {
        $unProcessedFiles = $this->project->project_documents()
            ->where('status', StatusEnum::QUEUED->value)
            ->get();

        foreach ($unProcessedFiles as $document) {
            ProcessProjectDocumentJob::dispatch($document);
        }
    }

}
