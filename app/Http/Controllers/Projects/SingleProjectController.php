<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Projects;

use App\Enums\ProjectStageEnum;
use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Jobs\Brainstorming\CreateIdeasFromProjectDocumentsJob;
use App\Jobs\Brainstorming\ProcessProjectDocumentsJob;
use App\Jobs\Ideating\CreatePrototypeRequestsFromIdeasJob;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectServices\ProjectDocumentService;
use Random\RandomException;

class SingleProjectController extends Controller
{
    public function getProject(string $id): array
    {
        $user = User::safeInstance(auth()->user());

        return [
            'project' => ProjectResource::make($user->projects()->where('id', $id)->firstOrFail()),
            'result' => 1,
        ];
    }

    /**
     * @throws RandomException
     * @throws \JsonException
     */
    public function uploadDocuments(string $id): array
    {
        $user = User::safeInstance(auth()->user());
        $project = $user->projects()->where('id', $id)->firstOrFail();

        request()->validate(
            [
                'file' => 'required|file|mimes:pdf,txt,mp4,mp3,png,jpg|max:512000',
            ],
            [
                'file.required' => 'Please upload at least one document.',
                'file.file' => 'The uploaded file must be a valid file.',
                'file.mimes' => 'Only PDF, TXT, MP4, MP3, JPG, PNG files are allowed.',
                'file.max' => 'The uploaded file may not be greater than 500MB.',
            ]
        );

        foreach (request()->files as $file) {
            if ($file->isValid()) {
                ProjectDocumentService::appendDocument($project, $file);
                ProcessProjectDocumentsJob::dispatch($project);
            } else {
                return [
                    'error' => 'Invalid file upload',
                    'result' => 0,
                ];
            }

        }

        return [
            'project' => ProjectResource::make($project),
            'result' => 1,
        ];
    }

    public function updateProjectStage(string $id): array
    {
        $user = User::safeInstance(auth()->user());
        $project = $user->projects()->where('id', $id)->firstOrFail();

        request()->validate(
            [
                'stage' => 'required|in:' . implode(',', ProjectStageEnum::getValues())
            ],
            [
                'stage.required' => 'Stage is required.',
                'stage.in' => 'Invalid stage value.',
            ]
        );

        $project->update(['stage' => request('stage')]);

        return [
            'project' => ProjectResource::make($project),
            'result' => 1,
        ];
    }

    public function updateProjectStatus(string $id): array
    {
        $user = User::safeInstance(auth()->user());
        $project = $user->projects()->where('id', $id)->firstOrFail();

        $originalStatus = $project->status;

        request()->validate(
            [
                'status' => 'required|in:' . implode(',', StatusEnum::getValues())
            ],
            [
                'status.required' => 'Status is required.',
                'status.in' => 'Invalid status value.',
            ]
        );

        $project->update(['status' => request('status')]);
        $project->refresh();

        if ($originalStatus !== request('status')) {
            $this->doActionsAfterChange($project, request('extra') ?? []);
        }

        return [
            'project' => ProjectResource::make($project),
            'result' => 1,
        ];
    }

    private function doActionsAfterChange(Project $project, array $extra): void
    {
        switch ($project->status . '-' . $project->stage) {
            case StatusEnum::QUEUED->value . '-' . ProjectStageEnum::BRAINSTORMING->value:
                CreateIdeasFromProjectDocumentsJob::dispatch($project);
                break;
            case StatusEnum::QUEUED->value . '-' . ProjectStageEnum::IDEATING->value:
                $selectedIdeas = $extra['selected_ideas'] ?? [];
                if (empty($selectedIdeas)) {
                    return;
                }
                foreach ($selectedIdeas as $ideaId) {
                    $idea = $project->project_ideas()->find($ideaId);
                    $idea?->update(['status' => StatusEnum::READY->value]);
                }
                CreatePrototypeRequestsFromIdeasJob::dispatch($project);
                break;
            default:
                break;
        }
    }
}

