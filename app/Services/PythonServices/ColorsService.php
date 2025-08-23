<?php

namespace App\Services\PythonServices;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class ColorsService
{
    public static function extractColors(string $imagePath): string
    {
        // Might need to change when going to production server
        $containerId = trim(getenv('HOSTNAME'));

        $inContainerPath = "/var/www/html" . Str::after($imagePath, base_path());

        $cmd = [
            'docker', 'run', '--rm',
            '--volumes-from', $containerId,
            'python-service:latest',
            'sh', '-c',
            "python3 /app/colors.py {$inContainerPath}"
        ];

        $process = Process::timeout(600)->run($cmd);

        if ($process->failed()) {
            throw new \RuntimeException("Image color extraction failed: " . $process->errorOutput());
        }

        $output = trim($process->output());

        $data = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        $result = [];

        $result[] = self::rgbToHex($data['dominant']);
        foreach ($data['palette'] as $color) {
            $result[] = self::rgbToHex($color);
        }

        return implode(',', $result);
    }

    private static function rgbToHex(array $rgbArray): string
    {
        // Found in stackoverflow: https://stackoverflow.com/questions/32962624/convert-rgb-to-hex-color-values-in-php
        return sprintf("#%02x%02x%02x", $rgbArray[0], $rgbArray[1], $rgbArray[2]);
    }
}
