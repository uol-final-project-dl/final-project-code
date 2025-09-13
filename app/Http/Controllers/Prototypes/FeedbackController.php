<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Prototypes;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Prototype;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FeedbackController extends Controller
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveFeedback(Project $project, Prototype $prototype): void
    {
        if ($project->id !== $prototype->project_idea->project_id
            || $prototype->user_id !== auth()->id()
            || $project->user_id !== auth()->id()
        ) {
            abort(403, 'Unauthorized access to this prototype.');
        }

        $prototype->update([
            'feedback_score' => request()->get('feedback_score', null),
        ]);
    }
}



