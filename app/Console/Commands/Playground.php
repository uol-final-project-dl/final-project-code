<?php

namespace App\Console\Commands;


use App\Services\VectorDB\ChunkEmbeddingService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;

class Playground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poc:playground';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Playground for new methods.';

    private ChunkEmbeddingService $chunkEmbeddingService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ChunkEmbeddingService $chunkEmbeddingService)
    {
        parent::__construct();

        $this->chunkEmbeddingService = $chunkEmbeddingService;
    }

    /**
     * @throws BindingResolutionException
     * @throws \JsonException
     */
    public function handle(): void
    {
        // Only needed to do it once.
        // Using a small set of files from a public GitHub repository
        /*
        $fileList = [
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/App.css',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/App.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/App.test.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/Button.css',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/Button.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/ButtonPanel.css',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/ButtonPanel.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/Display.css',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/component/Display.js',

            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/logic/calculate.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/logic/calculate.test.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/logic/isNumber.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/logic/operate.js',

            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/index.css',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/src/index.js',
            'https://raw.githubusercontent.com/andrewagain/calculator/refs/heads/master/package.json'
        ];

        foreach ($fileList as $fileUrl) {
            // FileParsingService::parseFile($fileUrl);
        }*/

        // Only needed to do it once.
        /*$files = CodeFile::all();

        foreach ($files as $codeFile) {
            $this->chunkEmbeddingService->saveFileEmbedding($codeFile);
        }*/

        //$codeGenerationService = CodeGenerationWithContextService::make();
        //dd($codeGenerationService->generateCode("Make the + method a - instead using the operate.js file"));


        /*$prototype = Prototype::query()->create([
            'user_id' => 1,
            'description' => "Make a calculator app that has a button to add two numbers together.",
            'status' => 'queued',
            'uuid' => (string)Str::uuid(),
        ]);

        GeneratePrototype::dispatch($prototype);*/

        /*GithubRepositoriesService::createBranch('1039162805', 'test-114');

        GithubRepositoriesService::createFile(
            '1039162805',
            'test-114',
            'test.txt',
            'This is a test file created from the command line.',
            'Initial commit from command line'
        );*/

    }
}
