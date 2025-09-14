<?php

namespace App\Jobs\Prototypes;

use App\Enums\StatusEnum;
use App\Models\Prototype;
use App\Services\CodeGeneration\PrototypeGenerationWithContextService;
use App\Services\WebSocket\NotifyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Pusher\ApiErrorException;
use Pusher\PusherException;

class GeneratePrototype implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    private Prototype $prototype;
    private bool $remix;
    private string|null $remixDescription;
    private bool $returnOutput;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(
        Prototype   $prototypeConstruct,
        bool        $remixConstruct = false,
        string|null $remixDescriptionConstruct = null,
        bool        $returnOutput = false
    )
    {
        $this->prototype = $prototypeConstruct;
        $this->remix = $remixConstruct ?? false;
        $this->remixDescription = $remixDescriptionConstruct ?? null;
        $this->returnOutput = $returnOutput;
    }

    /**
     * @throws PusherException
     * @throws ApiErrorException
     * @throws GuzzleException
     * @throws BindingResolutionException
     */
    public function handle(): ?array
    {
        $uuid = $this->prototype->uuid;
        $patchFile = "jobs/$uuid/patch-App.jsx";
        $workDirectory = storage_path("app/private/jobs/$uuid");
        $extraLogprobs = [];

        Storage::disk('local')->makeDirectory("jobs/$uuid");

        $prompt = $this->prototype->title . ' : ' . $this->prototype->description;

        if ($this->prototype->log && $this->prototype->log !== '') {
            $prompt .= "\n\n COMPILATION ERROR ON PREVIOUS GENERATION:" . $this->prototype->log . "\n\n";
        }

        // Call the LLM to generate the React code
        [$reactCode, $logprobs] = $this->generateWithLLM($prompt);

        Storage::disk('local')->put($patchFile, $reactCode);

        $result = $this->runCompilation($uuid);

        // Try to handle the result of the process
        if ($result->failed()) {
            $errorOutput = $result->errorOutput();

            $incomplete = preg_match(
                '/(Unexpected end of file|Unterminated string literal|Expected .* but found end of file)/i',
                $errorOutput
            );

            if ($incomplete) {
                [$newResult, $extraLogprobs] = $this->continueGeneratingWithLLM(
                    $prompt,
                    $patchFile,
                    $reactCode,
                    $uuid
                );

                if ($newResult->failed()) {
                    $this->prototype->update([
                        'status' => StatusEnum::FAILED->value,
                        'log' => $newResult->errorOutput(),
                    ]);
                    NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
                    return null;
                }
            } else {
                $this->prototype->update([
                    'status' => StatusEnum::FAILED->value,
                    'log' => $result->errorOutput(),
                ]);
                NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);
                return null;
            }
        }

        $zipPath = "$workDirectory/$uuid.zip";
        $result = Process::path("$workDirectory/dist")->run([
            'zip', '-qr', $zipPath, '.'
        ]);

        $path = Storage::disk('minio')->putFile("prototypes", $zipPath);

        $this->prototype->update([
            'status' => StatusEnum::READY->value,
            'bundle' => $path,
            'log' => $result->output(),
        ]);

        NotifyService::reloadUserPage($this->prototype->project_idea->project->user_id);

        if ($this->returnOutput) {
            return [$reactCode, array_merge($logprobs, $extraLogprobs)];
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    private function generateWithLLM(string $prompt): array
    {
        if ($this->remix && $this->remixDescription) {
            $oldCode = Storage::disk('local')->get("jobs/{$this->prototype->uuid}/patch-App.jsx");
            return PrototypeGenerationWithContextService::generate($this->prototype, $prompt, null, $oldCode, $this->remixDescription);
        }

        return PrototypeGenerationWithContextService::generate($this->prototype, $prompt, null, null, null, true);
    }

    /**
     * @throws \Exception
     */
    private function continueGeneratingWithLLM(string $prompt, string $patchFile, string $codeSoFar, string $uuid): array
    {
        if ($this->remix && $this->remixDescription) {
            $oldCode = Storage::disk('local')->get("jobs/{$this->prototype->uuid}/patch-App.jsx");
            [$rest, $logprobs] = PrototypeGenerationWithContextService::generate($this->prototype, $prompt, $codeSoFar, $oldCode, $this->remixDescription);
        } else {
            [$rest, $logprobs] = PrototypeGenerationWithContextService::generate($this->prototype, $prompt, $codeSoFar);
        }

        Storage::disk('local')->put($patchFile, $codeSoFar . "\n" . $rest);

        return [$this->runCompilation($uuid), $logprobs];
    }

    private function runCompilation(string $uuid): ProcessResult
    {
        // Might need to change when going to production server
        $containerId = trim(getenv('HOSTNAME'));

        $cmd = [
            'docker', 'run', '--rm',
            '--volumes-from', $containerId,
            'final-project-code-main-react-buildbox:latest',
            'sh', '-c',
            "jobDir=/var/www/html/storage/app/private/jobs/$uuid &&
             cp \$jobDir/patch-App.jsx /app/templates/base/src/App.jsx &&
             cd /app/templates/base &&
             yarn vite build --outDir \$jobDir/dist"
        ];

        return Process::run($cmd);
    }
}
