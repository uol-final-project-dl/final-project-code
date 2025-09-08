<?php

namespace Tests\Feature\Services\FFMPEG;

use App\Services\FFMPEG\FFMPEGService;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\Process\Process;
use Tests\AuthenticatedTestCase;

class FFMPEGServiceTest extends AuthenticatedTestCase
{

    /**
     * @throws \JsonException
     */
    #[RunInSeparateProcess]
    public function test_trim(): void
    {
        $tmpFilePath = storage_path('app/tmp/' . Str::uuid() . '_test_file.mp3');
        $outputFile = pathinfo($tmpFilePath, PATHINFO_DIRNAME)
            . '/'
            . pathinfo($tmpFilePath, PATHINFO_FILENAME)
            . '_trimmed.mp3';
        file_put_contents($tmpFilePath, 'Hello, World!');
        file_put_contents($outputFile, 'Hello, World!');

        $processMock = Mockery::mock('overload:' . Process::class);
        $processMock->shouldReceive('setTimeout')->with(null)->andReturnSelf();
        $processMock->shouldReceive('run')->andReturn(0);
        $processMock->shouldReceive('isSuccessful')->andReturnTrue();

        FFMPEGService::trim($tmpFilePath, 900);

        $this->assertTrue(true);
    }
}
