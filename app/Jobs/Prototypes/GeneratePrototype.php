<?php

namespace App\Jobs\Prototypes;

use App\Models\Prototype;
use App\Services\CodeGeneration\PrototypeGenerationWithContextService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use JsonException;

class GeneratePrototype implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private PrototypeGenerationWithContextService $prototypeGenerationWithContextService;

    /**
     * @throws BindingResolutionException
     */
    public function __construct(
        public Prototype $prototype,   // Eloquent model with user_id & description
    )
    {
        $this->prototypeGenerationWithContextService = PrototypeGenerationWithContextService::make();
    }

    /**
     * @throws JsonException
     */
    public function handle(): void
    {
        $uuid = $this->prototype->uuid;
        $patchRel = "jobs/{$uuid}/patch-App.jsx";
        $workDir = storage_path("app/private/jobs/{$uuid}");

        Storage::disk('local')->makeDirectory("jobs/{$uuid}");


        // Call the LLM to generate the React code
        $reactCode = $this->generateWithLLM($this->prototype->description);

        Storage::disk('local')->put($patchRel, $reactCode);

        // Might need to change when going to production
        $containerId = trim(getenv('HOSTNAME'));

        $cmd = [
            'docker', 'run', '--rm',
            '--volumes-from', $containerId,
            'brainstorm-to-prototype-react-buildbox:latest',
            'sh', '-c',
            "jobDir=/var/www/html/storage/app/private/jobs/{$uuid} &&
             cp \$jobDir/patch-App.jsx /app/templates/base/src/App.jsx &&
             cd /app/templates/base &&
             yarn vite build --outDir \$jobDir/dist"
        ];

        $result = Process::run($cmd);

        // Try to handle the result of the process
        if ($result->failed()) {
            $this->prototype->update([
                'status' => 'failed',
                'log' => $result->errorOutput(),
            ]);
            return;
        }

        $zipPath = "{$workDir}/{$uuid}.zip";
        $result = Process::path("{$workDir}/dist")->run([
            'zip', '-qr', $zipPath, '.'
        ]);

        $path = Storage::disk('minio')->putFile("prototypes", $zipPath);

        $this->prototype->update([
            'status' => 'ready',
            'bundle' => $path,
            'log' => $result->output(),
        ]);


    }

    /**
     * @throws JsonException
     */
    private function generateWithLLM(string $prompt): string
    {
        return $this->prototypeGenerationWithContextService->generate($prompt);
    }
}
