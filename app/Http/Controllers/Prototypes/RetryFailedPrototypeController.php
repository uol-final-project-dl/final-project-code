<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Prototypes;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Jobs\Prototypes\GeneratePrototype;
use App\Models\Project;
use App\Models\Prototype;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RetryFailedPrototypeController extends Controller
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function retryPrototype(Project $project, Prototype $prototype)
    {
        if ($project->id !== $prototype->project_idea->project_id
            || $prototype->user_id !== auth()->id()
            || $project->user_id !== auth()->id()
        ) {
            abort(403, 'Unauthorized access to this prototype.');
        }

        $prototype->update([
            'status' => StatusEnum::QUEUED->value,
        ]);

        GeneratePrototype::dispatch($prototype);
    }
}



