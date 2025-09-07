<?php

namespace App\Jobs\Brainstorming;

use App\Enums\ProjectStageEnum;
use App\Enums\StatusEnum;
use App\Models\CodeFile;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Services\IdeaGeneration\IdeaGenerationFromRepoService;
use App\Services\IdeaGeneration\IdeaGenerationService;
use App\Services\WebSocket\NotifyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateIdeasFromProjectDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 20000;

    private Project $project;
    private bool $returnOutput;

    public function __construct(
        Project $project,
        bool    $returnOutput = false
    )
    {
        $this->project = $project;
        $this->returnOutput = $returnOutput;
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     * @throws GuzzleException
     */
    public function handle(): ?array
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

        if ($project->github_repository_id) {
            $contextFiles = '';

            $project->refresh();
            $files = $project->code_files;

            foreach ($files as $file) {
                $file = CodeFile::safeInstance($file);
                $contextFiles .= "\n\n --- " . $file->path . '/' . $file->name . "--- \n" . $file->content;
            }

            [$answer, $logprobs] = IdeaGenerationFromRepoService::generateIdeas($provider, $context, $contextFiles);
        } else {
            [$answer, $logprobs] = IdeaGenerationService::generateIdeas($provider, $context);
        }

        $cleanedAnswer = preg_replace('/```(?:json)?\n?/', '', $answer);
        $cleanedAnswer = trim($cleanedAnswer, "\" \n\r\t");

        try {
            $ideas = json_decode($cleanedAnswer, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $ideas = [];
        }

        if (empty($ideas)) {
            $this->project->update([
                'status' => StatusEnum::REQUEST_DATA->value
            ]);
            return null;
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

        NotifyService::reloadUserPage($this->project->user_id);

        if ($this->returnOutput) {
            return [$answer, $logprobs];
        }

        return null;
    }

}
