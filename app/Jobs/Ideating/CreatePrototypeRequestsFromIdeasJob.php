<?php

namespace App\Jobs\Ideating;

use App\Enums\ProjectStageEnum;
use App\Enums\PrototypeTypeEnum;
use App\Enums\StatusEnum;
use App\Jobs\Prototypes\GeneratePrototype;
use App\Jobs\Prototypes\GeneratePullRequest;
use App\Models\Project;
use App\Models\ProjectIdea;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CreatePrototypeRequestsFromIdeasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    private Project $project;

    public function __construct(
        Project $project,
    )
    {
        $this->project = $project;
    }

    public function handle(): void
    {
        $project = Project::safeInstance($this->project);

        $ideas = $project->project_ideas()
            ->where('status', StatusEnum::READY->value)
            ->get();

        if (empty($ideas)) {
            $project->update([
                'status' => StatusEnum::REQUEST_DATA->value
            ]);
            return;
        }

        foreach ($ideas as $idea) {
            $idea = ProjectIdea::safeInstance($idea);
            if (!isset($idea->title, $idea->description)) {
                continue;
            }

            if ($idea->prototypes()
                ->where('status', StatusEnum::QUEUED->value)
                ->exists()) {
                // Skip if a prototype request is already queued for this idea
                continue;
            }

            $prototype = $idea->prototypes()->create([
                'user_id' => $project->user_id,
                'project_idea_id' => $idea->id,
                'title' => $idea->title,
                'description' => $idea->description,
                'status' => StatusEnum::QUEUED->value,
                'uuid' => (string)Str::uuid(),
                'type' => $project->github_repository_id ? PrototypeTypeEnum::PULL_REQUEST->value : PrototypeTypeEnum::DEMO->value,
            ]);

            if ($project->github_repository_id) {
                GeneratePullRequest::dispatch($prototype);
            } else {
                GeneratePrototype::dispatch($prototype);
            }
        }

        $project->update([
            'stage' => ProjectStageEnum::PROTOTYPING->value,
            'status' => StatusEnum::QUEUED->value
        ]);
    }

}
