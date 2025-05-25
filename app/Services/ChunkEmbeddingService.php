<?php

namespace App\Services\VectorKB;

use App\Article;
use App\Jobs\VectorKB\SyncChunkEmbedding;
use App\Models\VectorKB\VectorChunk;
use App\Services\ArticleService;
use App\Traits\HasMakeAble;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use League\HTMLToMarkdown\HtmlConverter;
use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Yethee\Tiktoken\EncoderProvider;

class ChunkEmbeddingService
{
    use HasMakeAble;

    private int $maxTokens = 700;
    private int $overlapTokens = 50;

    // constructor

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
    }

    public function codeToChunks($file): Collection
    {
        // First tokenise
        $provider = new EncoderProvider();
        $encoder = $provider->get('cl100k_base');
        $tokenIds = $encoder->encode($file->code);
        $tokenCount = count($tokenIds);

        $step = $this->maxTokens - $this->overlapTokens;
        $chunks = collect();
        for ($i = 0, $n = 1; $i < $tokenCount; $i += $step, $n++) {
            $slice = array_slice($tokenIds, $i, $this->maxTokens);
            $text = $encoder->decode($slice);

            $chunks->push(new VectorChunk(
                id: "{$file->id}#c{$n}",
                text: $text,
                // merge the metadata
                metadata: [
                    'name' => $file->name,
                    'url' => "/user/app/article/{$file->id}",
                    'type' => 'article',
                    'chunk' => $n,
                    'object_id' => $file->id,
                ],
            ));
        }
        return $chunks;
    }

    public function saveFileEmbedding($file): void
    {
        $chunks = $this->codeToChunks($file);

        foreach ($chunks as $chunk) {
            SyncChunkEmbedding::dispatch($chunk);
        }
    }
}

