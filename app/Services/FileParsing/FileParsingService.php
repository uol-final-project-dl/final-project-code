<?php

namespace App\Services\FileParsing;

use App\Models\CodeFile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class FileParsingService
{
    /**
     * @throws ConnectionException
     */
    public static function parseFile(string $fileUrl): CodeFile
    {
        $content = self::readContents($fileUrl);

        $info = self::pathInfoFromUrl($fileUrl);
        $extension = strtolower($info['extension'] ?? '');
        $type = $extension ? ".$extension" : '';

        $summary = self::makeSummary($content, $type);

        return CodeFile::query()->create([
            'name' => $info['basename'],
            'path' => $info['dirname'],
            'type' => $type,
            'summary' => $summary,
            'content' => $content,
        ]);
    }

    /**
     * @throws ConnectionException
     */
    private static function readContents(string $url): string
    {
        // Remote fetching the file
        if (Str::startsWith($url, ['http://', 'https://'])) {
            $resp = Http::timeout(8)->get($url);

            if (!$resp->ok()) {
                throw new RuntimeException("Failed to download $url – HTTP {$resp->status()}");
            }
            return $resp->body();
        }

        // If it is a local path
        if (!is_readable($url)) {
            throw new RuntimeException("File not readable: $url");
        }

        return file_get_contents($url);
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
