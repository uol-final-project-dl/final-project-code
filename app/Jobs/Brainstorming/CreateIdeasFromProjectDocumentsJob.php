<?php

namespace App\Jobs\Brainstorming;

use App\Enums\ProjectStageEnum;
use App\Enums\StatusEnum;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Services\IdeaGeneration\IdeaGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateIdeasFromProjectDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Project $project;

    public function __construct(
        Project $project,
    )
    {
        $this->project = $project;
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function handle(): void
    {
        $project = Project::safeInstance($this->project);

        $processedFiles = $this->project->project_documents()
            ->where('status', StatusEnum::READY->value)
            ->get();

        $context = 'Project Title: ' . $project->name;

        if ($project->description && $project->description !== '') {
            $context .= "\n\nProject Description: " . $project->description . "\n\n";
        }

        $contextDocuments = [];
        foreach ($processedFiles as $document) {
            $document = ProjectDocument::safeInstance($document);
            $contextDocuments[] = $document->content;
        }

        if (empty($contextDocuments) && $project->description === '') {
            $this->project->update([
                'status' => StatusEnum::REQUEST_DATA->value
            ]);
        }

        $contextDocuments[] = $project->description;

        $context .= implode("\n\n", $contextDocuments);

        $provider = $project->user->provider;

        $answer = IdeaGenerationService::generateIdeas($provider, $context);

        $cleanedAnswer = trim($answer, "\" \n\r\t");

        try {
            $ideas = json_decode($cleanedAnswer, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $ideas = [];
        }

        if (empty($ideas)) {
            $this->project->update([
                'status' => StatusEnum::REQUEST_DATA->value
            ]);
            return;
        }

        foreach ($ideas as $idea) {
            if (!isset($idea['title'], $idea['description']) || !is_array($idea)) {
                continue;
            }
            $this->project->project_ideas()->create([
                'title' => $idea['title'],
                'description' => $idea['description'],
                'status' => StatusEnum::REQUEST_DATA->value,
                'ranking' => $idea['ranking'] ?? 0,
            ]);
        }

        $this->project->update([
            'stage' => ProjectStageEnum::IDEATING->value,
            'status' => StatusEnum::REQUEST_DATA->value
        ]);
    }

}
