<?php

namespace App\Services\Whisper;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class WhisperService
{
    public static function transcribe($audioPath, $model = 'base'): false|string
    {
        $outputDir = storage_path('app/tmp');

        $command = [
            'whisper',
            $audioPath,
            '--model',
            $model,
            '--output_dir',
            $outputDir,
            '--output_format',
            'txt',
        ];

        try {
            $process = new Process($command);
            $process->setTimeout(null);
            $process->run();

            Log::error('Output: ' . $process->getOutput());
            Log::error('Error Output: ' . $process->getErrorOutput());

            if (!$process->isSuccessful()) {
                Log::error('Command: ' . implode(' ', $command));
                Log::error('Output: ' . $process->getOutput());
                Log::error('Error Output: ' . $process->getErrorOutput());
                throw new \RuntimeException($process->getErrorOutput());
            }
        } catch (\Exception $e) {
            // Handle the exception as needed, e.g., log it or rethrow it
            Log::error('Whisper transcription failed: ' . $e->getMessage());
            // More error info
            Log::error('Command: ' . implode(' ', $command));
            Log::error('Output: ' . $process->getOutput());
            Log::error('Error Output: ' . $process->getErrorOutput());
            // Return false to indicate failure

            return false;
        }

        $baseName = pathinfo($audioPath, PATHINFO_FILENAME);
        $transcriptionFile = $outputDir . '/' . $baseName . '.txt';

        $text = file_get_contents($transcriptionFile);

        unlink($transcriptionFile);

        return $text;
    }
}
