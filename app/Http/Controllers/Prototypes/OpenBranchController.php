<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Prototypes;

use App\Http\Controllers\Controller;
use App\Models\Prototype;
use App\Models\User;
use App\Services\Github\GithubRepositoriesService;

class OpenBranchController extends Controller
{
    public function redirectToBranch(Prototype $prototype)
    {
        $user = User::safeInstance(auth()->user());

        if ($prototype->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this prototype.');
        }

        $project = $prototype->project_idea->project;

        return redirect(GithubRepositoriesService::getRepositoryUrlToBranchDiff(
            $project->github_repository_id,
            $prototype->uuid
        ));
    }
}



