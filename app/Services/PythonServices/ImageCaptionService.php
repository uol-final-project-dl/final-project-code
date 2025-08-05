<?php

namespace App\Services\PythonServices;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class ImageCaptionService
{
    public static function caption(string $imagePath): string
    {
        // Might need to change when going to production server
        $containerId = trim(getenv('HOSTNAME'));

        $inContainerPath = "/var/www/html" . Str::after($imagePath, base_path());

        $cmd = [
            'docker', 'run', '--rm',
            '--volumes-from', $containerId,
            'python-service:latest',
            'sh', '-c',
            "python3 /app/caption.py {$inContainerPath}"
        ];

        $process = Process::timeout(600)->run($cmd);

        if ($process->failed()) {
            throw new \RuntimeException("Image captioning failed: " . $process->errorOutput());
        }

        return trim($process->output());
    }
}
