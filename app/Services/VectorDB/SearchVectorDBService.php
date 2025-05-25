<?php

namespace App\Services\VectorDB;

use App\Services\OpenAI\OpenAIEmbeddingsService;
use App\Traits\HasMakeAble;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchVectorDBService
{
    use HasMakeAble;

    private const TOP_K = 6;

    private const EXPANSION_K = 3;

    private const LEXICAL_K = 3;

    private const PRIMARY_FILE_LIMIT = 3;

    public static function searchFileChunks(string $prompt): Collection
    {
        // Embed user prompt using the same model
        $vector = OpenAIEmbeddingsService::embed($prompt);
        $vectorParam = '[' . implode(',', $vector) . ']';

        $initial = collect(DB::connection('vector')->select(
        /** @lang SQL */
            'SELECT DISTINCT ON (metadata->>\'repo_path\')
            id,
            content,
            metadata,
            1 - (embed <=> ?::vector) AS score
             FROM   kb_chunks
             ORDER  BY metadata->>\'repo_path\',
                      embed <=> ?::vector ASC
             LIMIT  ?',
            [$vectorParam, $vectorParam, self::PRIMARY_FILE_LIMIT]
        ));

        // To also get the files that are imported by the used files
        $relativePaths = collect();

        foreach ($initial as $row) {
            $meta = is_string($row->metadata)
                ? json_decode($row->metadata, true, 512, JSON_THROW_ON_ERROR)
                : (array)$row->metadata;

            if (!isset($meta['repo_path'])) {
                continue;
            }

            $baseDir = Str::beforeLast($meta['repo_path'], '/');

            foreach (($meta['imports'] ?? []) as $imp) {
                // Skip package names
                if (!Str::startsWith($imp, ['./', '../'])) {
                    continue;
                }

                $resolved = self::resolveRelativePath($baseDir, $imp);
                $relativePaths->push($resolved);
                $relativePaths->push(Str::afterLast($resolved, '/'));
            }
        }

        $relativePaths = $relativePaths->unique()->values();

        if ($relativePaths->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $relativePaths->count(), '?'));
            $bindings = $relativePaths->all();
            $bindings[] = self::EXPANSION_K;                       // LIMIT

            $expansion = collect(DB::connection('vector')->select(
            /** @lang SQL */
                "SELECT id,
                content,
                metadata,
                0.50 AS score
         FROM   kb_chunks
         WHERE  (metadata->>'repo_path') = ANY (ARRAY[$placeholders])
            OR  (metadata->>'file_name') = ANY (ARRAY[$placeholders])
         LIMIT  ?",
                $bindings
            ));

            $initial = $initial->merge($expansion);
        }

        // Fallback if nothing is found
        if ($initial->count() < self::TOP_K) {
            $missing = self::LEXICAL_K;
            $lexical = collect(DB::connection('vector')->select(
                'SELECT id,
                        content,
                        metadata,
                        0.40 AS score
                 FROM   kb_chunks
                 WHERE to_tsvector(
                           \'simple\',
                           (metadata->>\'summary\') || \' \' ||
                           (metadata->>\'file_name\')
                       ) @@ plainto_tsquery(\'simple\', ?)
                 LIMIT  ?',
                [$prompt, $missing]
            ));

            $initial = $initial->merge($lexical);
        }

        // return only unique results, sorted by score
        return $initial
            ->unique('id')
            ->sortByDesc('score')
            ->values();
    }

    private static function resolveRelativePath(string $baseDir, string $importPath): string
    {
        $segments = explode('/', ltrim($importPath, './'));
        $stack = explode('/', trim($baseDir, '/'));

        foreach ($segments as $seg) {
            if ($seg === '' || $seg === '.') {
                continue;
            }
            if ($seg === '..') {
                array_pop($stack);
            } else {
                $stack[] = $seg;
            }
        }
        return implode('/', $stack);
    }
}
