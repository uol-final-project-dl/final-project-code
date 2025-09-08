<?php

namespace App\Jobs\Prototypes;

use App\Enums\StatusEnum;
use App\Models\Prototype;
use App\Services\CodeGeneration\CodeGenerationWithContextService;
use App\Services\Github\GithubRepositoriesService;
use App\Services\WebSocket\NotifyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use JsonException;
use Pusher\ApiErrorException;
use Pusher\PusherException;

class GeneratePullRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    private bool $returnOutput;

    public function __construct(
        public Prototype $prototype,
        bool             $returnOutput = false,
    )
    {
        $this->returnOutput = $returnOutput;
    }

    /**
     * @return array|null
     * @throws BindingResolutionException
     * @throws GuzzleException
     * @throws JsonException
     * @throws ApiErrorException
     * @throws PusherException
     */
    public function handle(): ?array
    {
        $prompt = $this->prototype->title . ' : ' . $this->prototype->description;

        // Call the LLM to generate the React code
        [$codeFiles, $logprobs] = $this->generateWithLLM($prompt);

        if (Str::contains($codeFiles, "NEED_FILE")) {
            $this->prototype->update([
                'status' => StatusEnum::FAILED->value,
                'log' => 'LLM requires additional files to generate the code.',
            ]);
            NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
            return null;
        }

        try {
            $codeFiles = json_decode($codeFiles, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->prototype->update([
                'status' => StatusEnum::FAILED->value,
                'log' => 'LLM returned invalid JSON: ' . $e->getMessage(),
            ]);
            NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
            return null;
        }

        if (empty($codeFiles)) {
            $this->prototype->update([
                'status' => StatusEnum::FAILED->value,
                'log' => 'LLM did not return any code files.',
            ]);
            NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
            return null;
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

        if ($this->returnOutput) {
            return [json_encode($codeFiles, JSON_THROW_ON_ERROR), $logprobs];
        }

        return null;
    }

    /**
     * @throws JsonException
     * @throws BindingResolutionException
     */
    private function generateWithLLM(string $prompt): array
    {
        $codeGenerationWithContextService = CodeGenerationWithContextService::make();
        $project = $this->prototype->project_idea->project;
        $provider = $this->prototype->user->provider;
        return $codeGenerationWithContextService->generateCode($project, $provider, $prompt);
    }

}
