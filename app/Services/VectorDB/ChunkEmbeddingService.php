<?php

namespace App\Services\VectorDB;

use App\Jobs\VectorDB\SyncChunkEmbedding;
use App\Models\VectorDB\VectorChunk;
use App\Traits\HasMakeAble;
use Illuminate\Support\Collection;

class ChunkEmbeddingService
{
    use HasMakeAble;

    private int $maxLines = 300;
    private int $overlapLines = 20;

    public function codeToChunks($codeFile): Collection
    {
        $lines = explode("\n", $codeFile->content);
        $totalLines = count($lines);

        $step = $this->maxLines - $this->overlapLines;
        $chunks = collect();

        for ($i = 0, $n = 1; $i < $totalLines; $i += $step, $n++) {
            $slice = array_slice($lines, $i, $this->maxLines);
            $text = implode("\n", $slice);

            $chunks->push(new VectorChunk(
                id: "{$codeFile->id}#c{$n}",
                text: $text,
                metadata: [
                    'name' => $codeFile->name,
                    'url' => "/user/app/article/{$codeFile->id}",
                    'type' => 'article',
                    'chunk' => $n,
                    'object_id' => $codeFile->id,
                ],
            ));
        }

        return $chunks;
    }

    public function saveFileEmbedding($codeFile): void
    {
        $chunks = $this->codeToChunks($codeFile);

        foreach ($chunks as $chunk) {
            SyncChunkEmbedding::dispatch($chunk);
        }
    }
}

