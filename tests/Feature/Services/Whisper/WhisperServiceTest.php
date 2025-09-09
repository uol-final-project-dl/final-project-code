<?php

namespace Tests\Feature\Services\Whisper;

use App\Services\Whisper\WhisperService;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class WhisperServiceTest extends TestCase
{

    /**
     * @throws \JsonException
     */
    #[RunInSeparateProcess]
    public function test_transcribe(): void
    {
        $tmpFilePath = storage_path('app/tmp/' . Str::uuid() . '_test_file.mp3');
        $outputFile = pathinfo($tmpFilePath, PATHINFO_DIRNAME)
            . '/'
            . pathinfo($tmpFilePath, PATHINFO_FILENAME)
            . '.txt';
        file_put_contents($tmpFilePath, 'Hello, World!');
        file_put_contents($outputFile, 'Hello, World!');

        $processMock = Mockery::mock('overload:' . Process::class);
        $processMock->shouldReceive('setTimeout')->with(null)->andReturnSelf();
        $processMock->shouldReceive('run')->andReturn(0);
        $processMock->shouldReceive('isSuccessful')->andReturnTrue();
        $processMock->shouldReceive('getOutput')->andReturn('Transcription output');
        $processMock->shouldReceive('getErrorOutput')->andReturn('');

        WhisperService::transcribe($tmpFilePath);

        $this->assertTrue(true);
    }
}
