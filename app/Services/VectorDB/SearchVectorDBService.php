<?php

namespace App\Services\VectorDB;

use App\Services\OpenAI\OpenAIEmbeddingsService;
use App\Traits\HasMakeAble;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;

class SearchVectorDBService
{
    use HasMakeAble;

    private const TOP_K = 20;

    private const EXPANSION_K = 10;

    private const LEXICAL_K = 3;

    private const PRIMARY_FILE_LIMIT = 3;

    /**
     * @throws JsonException
     */
    public static function searchFileChunks(int $projectId, string $prompt): Collection
    {
        // Embed user prompt using the same model
        $vector = OpenAIEmbeddingsService::embed($prompt);
        $placeholders = implode(',', $vector);
        $vectorLit = "vector'[$placeholders]'";

        $initial = collect(DB::connection('vector')->select(
            "
                SELECT id, content, metadata,
                       1 - (embed <=> $vectorLit) AS score
                FROM   kb_chunks
                WHERE  metadata ->> 'project_id' = ?
                ORDER  BY embed <=> $vectorLit
                LIMIT  ?
            ",
            [$projectId, self::PRIMARY_FILE_LIMIT]
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

            self::prepareRelativePats($meta, $relativePaths);
        }

        $relativePaths = $relativePaths->unique()->values();

        if ($relativePaths->isNotEmpty()) {
            $expansion = self::getRelativeChunks($projectId, $relativePaths);

            $moreRelativePaths = collect();

            foreach ($expansion as $chunk) {
                $meta = is_string($chunk->metadata)
                    ? json_decode($chunk->metadata, true, 512, JSON_THROW_ON_ERROR)
                    : (array)$chunk->metadata;

                if (!isset($meta['repo_path'])) {
                    continue;
                }

                self::prepareRelativePats($meta, $moreRelativePaths);
            }

            $moreRelativePaths = $moreRelativePaths->unique()->values();

            if ($moreRelativePaths->isNotEmpty()) {
                $expansion2 = self::getRelativeChunks($projectId, $moreRelativePaths);
                $expansion = $expansion->merge($expansion2);
            }

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
                    AND   metadata ->> \'project_id\' = ?
                 LIMIT  ?',
                [$prompt, $projectId, $missing]
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
        $segments = explode('/', $importPath);
        $stack = explode('/', $baseDir);

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

    private static function prepareRelativePats(array $meta, $relativePaths): void
    {
        $baseDir = $meta['repo_path'];
        $knownExtensions = ['.js'];
        foreach (($meta['imports'] ?? []) as $import) {
            // Skip package names
            if (!Str::startsWith($import, ['./', '../'])) {
                continue;
            }

            $resolved = self::resolveRelativePath($baseDir, $import);
            $relativePaths->push($resolved);

            if (!Str::contains(Str::afterLast($resolved, '/'), '.')) {
                foreach ($knownExtensions as $ext) {
                    $relativePaths->push($resolved . $ext);
                    $relativePaths->push($resolved . '/index' . $ext);
                }
            }
        }
    }

    private static function getRelativeChunks($projectId, $relativePaths): Collection
    {
        $pairs = $relativePaths->map(function ($p) {
            return [
                Str::beforeLast($p, '/'),
                Str::afterLast($p, '/')
            ];
        })->filter(fn($pair) => $pair[0] !== '' && $pair[1] !== '')
            ->unique()
            ->values();


        $sqlTuples = implode(',', array_fill(0, $pairs->count(), '(?, ?)'));
        $bindings = $pairs->flatten()->all();
        $bindings[] = self::EXPANSION_K;
        $bindings[] = $projectId;

        return collect(DB::connection('vector')->select(
        /** @lang SQL */
            "SELECT id,
                content,
                metadata,
                0.50 AS score
         FROM   kb_chunks
         WHERE  (metadata->>'repo_path', metadata->>'file_name')
                IN ($sqlTuples)
                AND metadata ->> 'project_id' = ?
         LIMIT  ?",
            $bindings
        ));
    }
}
