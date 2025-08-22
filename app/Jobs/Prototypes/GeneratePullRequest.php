<?php

namespace App\Jobs\Prototypes;

use App\Enums\StatusEnum;
use App\Models\Prototype;
use App\Services\CodeGeneration\CodeGenerationWithContextService;
use App\Services\Github\GithubRepositoriesService;
use App\Services\WebSocket\NotifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GeneratePullRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    private CodeGenerationWithContextService $codeGenerationWithContextService;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(
        public Prototype $prototype
    )
    {
        $this->codeGenerationWithContextService = CodeGenerationWithContextService::make();
    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Pusher\ApiErrorException
     * @throws \Pusher\PusherException
     */
    public function handle(): void
    {
        $prompt = $this->prototype->title . ' : ' . $this->prototype->description;

        // Call the LLM to generate the React code
        $codeFiles = $this->generateWithLLM($prompt);

        if (Str::contains($codeFiles, "NEED_FILE")) {
            $this->prototype->update([
                'status' => StatusEnum::FAILED->value,
                'log' => 'LLM requires additional files to generate the code.',
            ]);
            NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
            return;
        }

        $codeFiles = json_decode($codeFiles, true, 512, JSON_THROW_ON_ERROR);

        if (empty($codeFiles)) {
            $this->prototype->update([
                'status' => StatusEnum::FAILED->value,
                'log' => 'LLM did not return any code files.',
            ]);
            NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
            return;
        }

        GithubRepositoriesService::createBranch(
            $this->prototype->project_idea->project->github_repository_id,
            $this->prototype->uuid
        );

        foreach ($codeFiles as $codeFile) {
            if ($codeFile['action'] === 'modify') {
                GithubRepositoriesService::updateFile(
                    $this->prototype->project_idea->project->github_repository_id,
                    $this->prototype->uuid,
                    $codeFile['repo_path'],
                    $codeFile['content'],
                    $this->prototype->title . ' - ' . $this->prototype->description
                );
            } elseif ($codeFile['action'] === 'create') {
                GithubRepositoriesService::createFile(
                    $this->prototype->project_idea->project->github_repository_id,
                    $this->prototype->uuid,
                    $codeFile['repo_path'],
                    $codeFile['content'],
                    $this->prototype->title . ' - ' . $this->prototype->description
                );
            }
        }

        // Create a pull request with the changes
        GithubRepositoriesService::createPullRequest(
            $this->prototype->project_idea->project->github_repository_id,
            $this->prototype->uuid,
            $this->prototype->title,
            $this->prototype->description
        );

        $this->prototype->update([
            'status' => StatusEnum::READY->value,
        ]);

        NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
    }

    /**
     * @throws \JsonException
     */
    private function generateWithLLM(string $prompt): string
    {
        $projectId = $this->prototype->project_idea->project_id;
        $provider = $this->prototype->user->provider;
        return $this->codeGenerationWithContextService->generateCode($projectId, $provider, $prompt);
    }

}
