<?php

namespace App\Console\Commands;

use App\Services\VectorDB\ChunkEmbeddingService;
use Illuminate\Console\Command;

class Playground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vectorkb:embed-all-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add embeddings for all articles.';

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

    public function handle(): void
    {
        $codeFile = '/path/to/your/file.txt';
        $this->chunkEmbeddingService->saveFileEmbedding($codeFile);
    }
}
