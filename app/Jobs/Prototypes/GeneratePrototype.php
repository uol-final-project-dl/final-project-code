<?php

namespace App\Jobs\Prototypes;

use App\Enums\StatusEnum;
use App\Models\Prototype;
use App\Services\CodeGeneration\PrototypeGenerationWithContextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class GeneratePrototype implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    private PrototypeGenerationWithContextService $prototypeGenerationWithContextService;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(
        public Prototype $prototype,
    )
    {
        $this->prototypeGenerationWithContextService = PrototypeGenerationWithContextService::make();
    }

    public function handle(): void
    {
        $uuid = $this->prototype->uuid;
        $patchFile = "jobs/$uuid/patch-App.jsx";
        $workDirectory = storage_path("app/private/jobs/$uuid");

        Storage::disk('local')->makeDirectory("jobs/$uuid");


        // Call the LLM to generate the React code
        $reactCode = $this->generateWithLLM($this->prototype->title . ' : ' . $this->prototype->description);

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
                $newResult = $this->continueGeneratingWithLLM(
                    $patchFile,
                    $reactCode,
                    $uuid
                );

                if ($newResult->failed()) {
                    $this->prototype->update([
                        'status' => StatusEnum::FAILED->value,
                        'log' => $newResult->errorOutput(),
                    ]);
                    return;
                }
            } else {
                $this->prototype->update([
                    'status' => StatusEnum::FAILED->value,
                    'log' => $result->errorOutput(),
                ]);
                return;
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
    }

    private function generateWithLLM(string $prompt): string
    {
        return $this->prototypeGenerationWithContextService->generate($prompt);
    }

    private function continueGeneratingWithLLM(string $patchFile, string $codeSoFar, string $uuid): ProcessResult
    {
        $rest = $this->prototypeGenerationWithContextService->generate($this->prototype->title . ' : ' . $this->prototype->description, $codeSoFar);

        Storage::disk('local')->put($patchFile, $codeSoFar . "\n" . $rest);

        return $this->runCompilation($uuid);
    }

    private function runCompilation(string $uuid): ProcessResult
    {
        // Might need to change when going to production server
        $containerId = trim(getenv('HOSTNAME'));

        $cmd = [
            'docker', 'run', '--rm',
            '--volumes-from', $containerId,
            'brainstorm-to-prototype-react-buildbox:latest',
            'sh', '-c',
            "jobDir=/var/www/html/storage/app/private/jobs/$uuid &&
             cp \$jobDir/patch-App.jsx /app/templates/base/src/App.jsx &&
             cd /app/templates/base &&
             yarn vite build --outDir \$jobDir/dist"
        ];

        return Process::run($cmd);
    }
}
