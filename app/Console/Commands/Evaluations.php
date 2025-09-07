<?php

namespace App\Console\Commands;

use App\Enums\PrototypeTypeEnum;
use App\Enums\ProviderEnum;
use App\Enums\StatusEnum;
use App\Jobs\Brainstorming\CreateIdeasFromProjectDocumentsJob;
use App\Jobs\Brainstorming\ProcessProjectDocumentJob;
use App\Jobs\Prototypes\GeneratePrototype;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectServices\ProjectDocumentService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Pusher\ApiErrorException;
use Pusher\PusherException;
use Random\RandomException;

class Evaluations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utils:evaluations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs all the evaluations.';

    private int $idx = 0;

    /**
     * @throws RandomException
     * @throws JsonException
     * @throws PusherException
     * @throws BindingResolutionException
     * @throws ApiErrorException
     * @throws GuzzleException
     */
    public function handle(): void
    {
        //$providers = ProviderEnum::getValues();
        $providers = [ProviderEnum::GOOGLE->value];
        $user = User::query()->first();
        $outputPath = 'eval_dataset.jsonl';

        $dataset = [
            'AtlasPM' => [
                'AtlasPM.pdf',
                //'AtlasPM-1.png',
                //'AtlasPM-2.png'
            ],
            /*'Helios' => [
                'Helios.pdf',
                'Helios-1.png'
            ],
            'FungalFrontier' => [
                'FungalFrontier.pdf',
                'FungalFrontier-1.png'
            ],*/
        ];

        $metrics = [];

        // Set config to run jobs synchronously
        config(['queue.default' => 'sync']);

        foreach ($providers as $provider) {
            $user->update([
                'provider' => $provider
            ]);

            $metrics[$provider] = [];

            foreach ($dataset as $caseName => $caseFiles) {
                $this->info("Running evaluation for provider: $provider, case: $caseName");
                $metrics[$provider][$caseName] = [];

                // Create Project
                $project = $this->createProject($user, $caseName);

                // Process files
                $startProcessTime = microtime(true);
                $this->processFiles($project, $caseFiles);
                $endProcessTime = microtime(true);
                $metrics[$provider][$caseName] = [
                    'file_count' => count($caseFiles),
                    'file_process_time_seconds' => round($endProcessTime - $startProcessTime, 2),
                ];

                //Generate Ideas
                $startIdeaTime = microtime(true);
                [$ideasString, $ideaLogprobs] = $this->generateIdeas($project);
                $endIdeaTime = microtime(true);

                if ($ideasString) {
                    $ideas = json_decode($ideasString, true, 512, JSON_THROW_ON_ERROR);
                    $metrics[$provider][$caseName]['idea_count'] = count($ideas);
                    $metrics[$provider][$caseName]['idea_fails'] = (count($ideas) === 0) ? 1.0 : 0.0;
                    $metrics[$provider][$caseName]['idea_generation_time_seconds'] = round($endIdeaTime - $startIdeaTime, 2);
                    $metrics[$provider][$caseName]['idea_perplexity'] = $this->calculatePerplexity($ideaLogprobs);
                } else {
                    $metrics[$provider][$caseName]['idea_count'] = 0;
                    $metrics[$provider][$caseName]['idea_fails'] = 1.0;
                    $metrics[$provider][$caseName]['idea_generation_time_seconds'] = round($endIdeaTime - $startIdeaTime, 2);
                    $metrics[$provider][$caseName]['idea_perplexity'] = 0.0;
                    continue;
                }

                // Generate prototypes
                [$metrics[$provider][$caseName], $prototypeCodes] = $this->generatePrototypes($project, $metrics[$provider][$caseName]);

                $this->addToOutput([
                    'provider' => $provider,
                    'case' => $caseName,
                    'metrics' => $metrics[$provider][$caseName],
                    'ideas' => $ideas,
                    'prototypes' => $prototypeCodes
                ], $outputPath);

                $this->info("Finished evaluation for provider: $provider, case: $caseName");
            }


        }

        $this->info("Evaluation Metrics:");
        $this->info(json_encode($metrics, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

    private function createProject(User $user, string $caseName): Project
    {
        // Create project
        return $user->projects()->create([
            'name' => $caseName
        ]);
    }

    /**
     * @throws RandomException
     * @throws JsonException
     */
    private function processFiles(Project $project, array $caseFiles): void
    {
        foreach ($caseFiles as $file) {
            // I'm saving the files in storage/app/public/evaluations/
            $filePath = storage_path('app/public/evaluations/' . $file);

            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                if ($content !== false) {
                    // I need to convert to UploadedFile so that it works with the existing service
                    $file = new UploadedFile($filePath, $file, mime_content_type($filePath), null, true);
                    ProjectDocumentService::appendDocument($project, $file);
                    $project->refresh();
                    $document = $project->project_documents()->orderBy('id', 'desc')->first();
                    ProcessProjectDocumentJob::dispatch($document);
                    $this->info("Processed file: $filePath");
                } else {
                    $this->error("Failed to read file: $filePath");
                }
            } else {
                $this->error("File does not exist: $filePath");
            }
        }
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    private function generateIdeas(Project $project): ?array
    {
        $job = new CreateIdeasFromProjectDocumentsJob($project, true);

        [$ideasString, $logprobs] = $job->handle();

        if ($ideasString) {
            $this->info("Generated Ideas correctly.");
            return [$ideasString, $logprobs];
        }

        $this->info("Failed generating ideas.");
        return null;
    }

    /**
     * @throws PusherException
     * @throws BindingResolutionException
     * @throws ApiErrorException
     * @throws GuzzleException
     */
    private function generatePrototypes(Project $project, array $metrics): array
    {
        $metrics['prototype_count'] = 0;
        $metrics['prototype_fails'] = 0;
        $metrics['prototype_generation_time_seconds'] = 0.0;
        $metrics['average_prototype_time_seconds'] = 0.0;

        $outputs = [];
        foreach ($project->project_ideas()->limit(1)->get() as $idea) {
            $this->info("Generating prototype for $idea->title");

            $metrics['prototype_count']++;
            $idea->update(['status' => StatusEnum::READY->value]);

            $prototype = $idea->prototypes()->create([
                'user_id' => $project->user_id,
                'project_idea_id' => $idea->id,
                'title' => $idea->title,
                'description' => $idea->description,
                'status' => StatusEnum::QUEUED->value,
                'uuid' => (string)Str::uuid(),
                'type' => $project->github_repository_id ? PrototypeTypeEnum::PULL_REQUEST->value : PrototypeTypeEnum::DEMO->value,
            ]);

            $startPrototypeTime = microtime(true);
            $generatePrototype = new GeneratePrototype($prototype, false, null, true);
            [$outputString, $outputLogprobs] = $generatePrototype->handle();
            $endPrototypeTime = microtime(true);

            $metrics['prototype_generation_time_seconds'] += round($endPrototypeTime - $startPrototypeTime, 2);

            $prototype->refresh();
            if ($prototype->status !== StatusEnum::READY->value) {
                $metrics['prototype_fails']++;
                $this->info("Failed generating prototype for idea: " . $idea->title);
            } else {
                $this->info("Generated prototype for idea: " . $idea->title);
                $outputs[] = [
                    'idea' => $idea->title . ' : ' . $idea->description,
                    'code' => $outputString,
                    'perplexity' => $this->calculatePerplexity($outputLogprobs)
                ];
            }

            $this->info("Generated prototype for idea: " . $idea->title);
        }

        if ($metrics['prototype_count'] > 0) {
            $metrics['average_prototype_time_seconds'] = round($metrics['prototype_generation_time_seconds'] / $metrics['prototype_count'], 2);
            $metrics['prototype_fail_rate'] = round($metrics['prototype_fails'] / $metrics['prototype_count'], 2);
        }

        return [$metrics, $outputs];
    }

    /**
     * @throws JsonException
     */
    private function addToOutput(array $data, string $outputPath): void
    {
        // append one JSON line
        if ($this->idx === 0) {
            Storage::disk('local')->put(
                $outputPath,
                json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
            );
            $this->idx++;
        } else {
            Storage::disk('local')->append(
                $outputPath,
                json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)
            );
        }
    }

    private function calculatePerplexity(array $logprobs): float
    {
        // Following my source:
        // perplexity_score = np.exp(-np.mean(logprobs))

        $tokenLogprobs = array_map(fn($lp) => $lp['logprob'] ?? -9999.0, $logprobs);

        $totalLogProb = 0.0;
        $tokenCount = count($tokenLogprobs);

        foreach ($tokenLogprobs as $logprob) {
            $totalLogProb += $logprob;
        }

        if ($tokenCount === 0) {
            return 0.0;
        }

        $avgLogProb = $totalLogProb / $tokenCount;

        return exp(-$avgLogProb);
    }


}
