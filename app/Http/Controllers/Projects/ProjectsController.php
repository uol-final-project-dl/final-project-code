<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\User;

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
        ]);

        return [
            'project' => $project,
            'result' => 1,
        ];
    }
}

