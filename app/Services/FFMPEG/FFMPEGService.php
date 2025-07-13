<?php

namespace App\Services\FFMPEG;

use Symfony\Component\Process\Process;

class FFMPEGService
{
    public static function trim(string $inputFile, int $duration = 900): void
    {
        $outputFile = pathinfo($inputFile, PATHINFO_DIRNAME)
            . '/'
            . pathinfo($inputFile, PATHINFO_FILENAME)
            . '_trimmed.mp3';

        $command = [
            'ffmpeg',
            '-y',
            '-i', $inputFile,
            '-t', (string)$duration,
            '-c', 'copy',
            $outputFile,
        ];

        $process = new Process($command);
        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        rename($outputFile, $inputFile);
    }
}
