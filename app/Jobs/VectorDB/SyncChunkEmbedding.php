<?php

namespace App\Jobs\VectorDB;

use App\Models\VectorDB\VectorChunk;
use App\Services\OpenAI\OpenAIEmbeddingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use JsonException;

class SyncChunkEmbedding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private VectorChunk $chunk;

    public function __construct(VectorChunk $chunk)
    {
        $this->chunk = $chunk;
    }

    /**
     * @throws JsonException
     */
    public function handle(): void
    {
        $vector = OpenAIEmbeddingsService::embed($this->chunk->text);
        $vectorLiteral = '[' . implode(',', $vector) . ']';

        DB::connection('vector')->update(
            'INSERT INTO kb_chunks (id, content, metadata, embed)
                 VALUES (?, ?, ?, ?)
                 ON CONFLICT (id) DO UPDATE
                       SET content = EXCLUDED.content,
                           metadata = EXCLUDED.metadata,
                           embed   = EXCLUDED.embed',
            [
                $this->chunk->id,
                $this->chunk->text,
                json_encode($this->chunk->metadata, JSON_THROW_ON_ERROR),
                $vectorLiteral
            ]
        );
    }
}
