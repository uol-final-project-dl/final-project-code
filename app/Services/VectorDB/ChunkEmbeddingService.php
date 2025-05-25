<?php

namespace App\Services\VectorDB;

use App\Jobs\VectorDB\SyncChunkEmbedding;
use App\Models\CodeFile;
use App\Models\VectorDB\VectorChunk;
use App\Traits\HasMakeAble;
use Illuminate\Support\Collection;

class ChunkEmbeddingService
{
    use HasMakeAble;

    private int $maxLines = 300;
    private int $overlapLines = 20;

    public function codeToChunks(CodeFile $codeFile): Collection
    {
        $fileHash = hash('sha256', $codeFile->content);
        $imports = $this->extractImports($codeFile->content);
        $exports = $this->extractExports($codeFile->content);

        $baseMetadata = [
            'file_name' => $codeFile->name,
            'repo_path' => $codeFile->path,
            'file_type' => $codeFile->type,
            'content_hash' => $fileHash,
            'imports' => $imports,
            'exports' => $exports,
            'summary' => $codeFile->summary,
            // TODO: wire a proper symbol inventory later
            'symbol_inventory' => implode(',', $exports),
        ];

        $lines = explode("\n", $codeFile->content);
        $totalLines = count($lines);

        $step = $this->maxLines - $this->overlapLines;
        $chunks = collect();

        for ($i = 0, $n = 1; $i < $totalLines; $i += $step, $n++) {
            $slice = array_slice($lines, $i, $this->maxLines);
            $text = implode("\n", $slice);

            $chunkMetadata = $baseMetadata;
            $chunkMetadata['chunk'] = $n;

            $chunks->push(new VectorChunk(
                id: "{$codeFile->id}#c{$n}",
                text: $text,
                metadata: $chunkMetadata,
            ));
        }

        return $chunks;
    }

    public function saveFileEmbedding(CodeFile $codeFile): void
    {
        $chunks = $this->codeToChunks($codeFile);

        foreach ($chunks as $chunk) {
            SyncChunkEmbedding::dispatch($chunk);
        }
    }

    // The extractImports method is found online not written by me
    private function extractImports(string $content): array
    {
        preg_match_all('/^\s*import\s+[^\'"]+\s+from\s+[\'"]([^\'"]+)[\'"];/m',
            $content, $m1);

        preg_match_all('/^\s*import\s+[\'"]([^\'"]+)[\'"];/m',
            $content, $m2);

        return array_values(array_unique([...$m1[1], ...$m2[1]]));
    }

    // The extractExports method is found online not written by me
    private function extractExports(string $content): array
    {
        $names = [];

        preg_match_all('/^\s*export\s+default\s+function\s+([A-Za-z0-9_$]+)/m',
            $content, $m1);
        $names = [...$names, ...$m1[1]];

        preg_match_all('/^\s*export\s+(?:async\s+)?(?:function|class|const|let|var)\s+([A-Za-z0-9_$]+)/m',
            $content, $m2);
        $names = [...$names, ...$m2[1]];

        preg_match_all('/^\s*export\s+{\s*([^}]+)\s*}/m', $content, $m3);
        foreach ($m3[1] as $list) {
            foreach (preg_split('/\s*,\s*/', $list) as $item) {
                $parts = preg_split('/\s+as\s+/i', trim($item));
                $names[] = $parts[0]; // keep original name
            }
        }

        return array_values(array_unique($names));
    }
}

