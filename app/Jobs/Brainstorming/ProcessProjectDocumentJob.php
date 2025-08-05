<?php

namespace App\Jobs\Brainstorming;

use App\Enums\StatusEnum;
use App\Models\ProjectDocument;
use App\Services\FFMPEG\FFMPEGService;
use App\Services\PythonServices\ImageCaptionService;
use App\Services\Whisper\WhisperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use Symfony\Component\Process\Process;

class ProcessProjectDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    private ProjectDocument $projectDocument;

    public function __construct(
        ProjectDocument $projectDocument,
    )
    {
        $this->projectDocument = $projectDocument;
    }

    public function handle(): void
    {
        if ($this->projectDocument->status !== StatusEnum::QUEUED->value) {
            return;
        }

        $fileContent = Storage::get($this->projectDocument->filename);
        if (!$fileContent) {
            $this->projectDocument->update([
                'status' => StatusEnum::FAILED->value,
                'error_message' => 'File not found',
            ]);
            return;
        }

        switch ($this->projectDocument->type) {
            case 'application/pdf':
                $this->processPdf($fileContent);
                break;
            case 'audio/mpeg':
                $this->processMp3($fileContent);
                break;
            case 'video/mp4':
                $this->processMp4($fileContent);
                break;
            case 'text/plain':
                $this->projectDocument->update([
                    'content' => $fileContent,
                    'status' => StatusEnum::READY->value,
                    'error_message' => null,
                ]);
                break;
            case 'image/png':
            case 'image/jpeg':
                $this->processImage($fileContent);
                break;
            default:
                $this->projectDocument->update([
                    'status' => StatusEnum::FAILED->value,
                    'errorMessage' => 'Unsupported file type',
                ]);
                break;
        }
    }

    private function processPdf($contents): void
    {
        $tmpFilePath = storage_path('app/tmp/' . Str::uuid() . '_project_document_' . $this->projectDocument->id . '.pdf');
        file_put_contents($tmpFilePath, $contents);

        $text = Pdf::getText($tmpFilePath);

        unlink($tmpFilePath);

        $this->projectDocument->update([
            'content' => $text,
            'status' => StatusEnum::READY->value,
            'error_message' => null,
        ]);
    }

    private function processMp4($contents): void
    {
        $tmpFilePath = storage_path('app/tmp/' . Str::uuid() . '_project_document_' . $this->projectDocument->id . '.mp4');
        $tmpOutputPath = storage_path('app/tmp/' . Str::uuid() . '_project_document_' . $this->projectDocument->id . '.mp3');

        file_put_contents($tmpFilePath, $contents);
        $command = [
            'ffmpeg',
            '-i', $tmpFilePath,
            '-q:a', '0',
            '-map', 'a',
            $tmpOutputPath,
        ];

        $process = new Process($command);
        $process->setTimeout(null);

        $process->run();

        if (!$process->isSuccessful()) {
            $this->projectDocument->update([
                'status' => StatusEnum::FAILED->value,
                'error_message' => 'Conversion failed',
            ]);
            return;
        }

        // Max 15 min for price and performance reasons
        FFMPEGService::trim($tmpOutputPath, 900);

        $text = WhisperService::transcribe($tmpOutputPath);

        unlink($tmpFilePath);
        unlink($tmpOutputPath);

        if ($text) {
            $this->projectDocument->update([
                'content' => $text,
                'status' => StatusEnum::READY->value,
                'error_message' => null,
            ]);
            return;
        }

        $this->projectDocument->update([
            'status' => StatusEnum::FAILED->value,
            'error_message' => 'Transcription failed',
        ]);
    }

    private function processMp3($contents): void
    {
        $tmpFilePath = storage_path('app/tmp/' . Str::uuid() . '_project_document_' . $this->projectDocument->id . '.mp3');
        file_put_contents($tmpFilePath, $contents);

        // Max 15 min for price and performance reasons
        FFMPEGService::trim($tmpFilePath, 900);

        $text = WhisperService::transcribe($tmpFilePath);

        unlink($tmpFilePath);

        if ($text) {
            $this->projectDocument->update([
                'content' => $text,
                'status' => StatusEnum::READY->value,
                'error_message' => null,
            ]);
            return;
        }

        $this->projectDocument->update([
            'status' => StatusEnum::FAILED->value,
            'error_message' => 'Transcription failed',
        ]);
    }

    private function processImage($contents): void
    {
        $tmpFilePath = storage_path('app/tmp/' . Str::uuid() . '_project_document_' . $this->projectDocument->id . '.image');
        file_put_contents($tmpFilePath, $contents);
        $caption = ImageCaptionService::caption($tmpFilePath);

        if ($caption) {
            $this->projectDocument->update([
                'content' => $caption,
                'status' => StatusEnum::READY->value,
                'error_message' => null,
            ]);
            return;
        }

        $this->projectDocument->update([
            'status' => StatusEnum::FAILED->value,
            'error_message' => 'Captioning failed',
        ]);
    }

}
