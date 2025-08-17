<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\Github\GithubRepositoriesService;
use App\Services\VectorDB\ChunkEmbeddingService;

class ProjectsController extends Controller
{
    public function getProjects(): array
    {
        $user = User::safeInstance(auth()->user());

        return [
            'projects' => $user->projects()->get(),
            'result' => 1,
        ];
    }

    public function createProject(): array
    {
        $user = User::safeInstance(auth()->user());

        $project = $user->projects()->create([
            'name' => request('name'),
            'description' => request('description'),
            'style_config' => request('style_config'),
            'github_repository_id' => request('github_repository_id'),
        ]);

        $project = Project::instance($project);

        if ($project && $project->github_repository_id) {
            GithubRepositoriesService::saveFilesFromRepo($project->id, $project->github_repository_id);

            $project->refresh();
            $files = $project->code_files;

            foreach ($files as $codeFile) {
                ChunkEmbeddingService::saveFileEmbedding($codeFile);
            }
        }

        return [
            'project' => $project,
            'result' => 1,
        ];
    }
}

