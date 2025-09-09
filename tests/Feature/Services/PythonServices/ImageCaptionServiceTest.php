<?php

namespace Tests\Feature\Services\PythonServices;

use App\Services\PythonServices\ImageCaptionService;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Random\RandomException;
use Tests\TestCase;

class ImageCaptionServiceTest extends TestCase
{
    /**
     * @throws RandomException
     * @throws \JsonException
     */
    #[RunInSeparateProcess]
    public function test_caption(): void
    {
        $file = '/tmp/testfile.txt';
        Process::fake();
        Process::shouldReceive('timeout')->andReturnSelf();
        Process::shouldReceive('run')->andReturnSelf();
        Process::shouldReceive('failed')->andReturn(false);
        Process::shouldReceive('output')->andReturn('Test Caption');

        $caption = ImageCaptionService::caption($file);

        $this->assertEquals('Test Caption', $caption);
    }
}
