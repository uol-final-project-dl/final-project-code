<?php

namespace App\Services\FileParsing;

use App\Models\CodeFile;
use Illuminate\Support\Str;

class FileParsingService
{
    private static array $extensions = [
        'js', 'ts', 'jsx', 'tsx', 'html', 'css', 'json', 'less', 'scss'
    ];

    public static function parseFile(int $projectId, string $fileUrl, string $content): void
    {
        $info = self::pathInfoFromUrl($fileUrl);
        $extension = strtolower($info['extension'] ?? '');

        // Only parse files with known extensions
        if (!in_array($extension, self::$extensions, true)) {
            return;
        }

        // Ignore files with -lock in the name
        if (str_contains($info['basename'], '-lock')) {
            return;
        }

        $type = $extension ? ".$extension" : '';

        $summary = self::makeSummary($content, $type);

        CodeFile::query()->create([
            'project_id' => $projectId,
            'name' => $info['basename'],
            'path' => $info['dirname'],
            'type' => $type,
            'summary' => $summary,
            'content' => $content,
        ]);
    }

    private static function pathInfoFromUrl(string $url): array
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;
        $info = pathinfo($path);

        return [
            'dirname' => $info['dirname'] ?? '',
            'basename' => $info['basename'] ?? '',
            'filename' => $info['filename'] ?? '',
            'extension' => $info['extension'] ?? '',
        ];
    }

    // The naive makeSummary method is found online not written by me
    private static function makeSummary(string $content, string $type = null): string
    {
        if ($type) {
            return '';
        }

        if (preg_match('/^\s*(?:\/\*\*(.*?)\*\/|\/\/\s*(.+))\s*\n/ms', $content, $m)) {
            $comment = trim($m[1] ?? $m[2]);
            return Str::limit(preg_replace('/\s+/', ' ', $comment), 160, ' …');
        }

        foreach (explode("\n", $content) as $line) {
            $clean = trim($line);
            if ($clean !== '') {
                return Str::limit($clean, 160, ' …');
            }
        }

        return '(no summary)';
    }
}
