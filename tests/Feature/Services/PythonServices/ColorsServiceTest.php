<?php

namespace Tests\Feature\Services\PythonServices;

use App\Services\PythonServices\ColorsService;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Random\RandomException;
use Tests\TestCase;

class ColorsServiceTest extends TestCase
{
    /**
     * @throws RandomException
     * @throws \JsonException
     */
    #[RunInSeparateProcess]
    public function test_extract_colors(): void
    {
        $file = '/tmp/testfile.txt';
        Process::fake();
        Process::shouldReceive('timeout')->andReturnSelf();
        Process::shouldReceive('run')->andReturnSelf();
        Process::shouldReceive('failed')->andReturn(false);
        Process::shouldReceive('output')->andReturn(json_encode([
            'dominant' => [255, 0, 0],
            'palette' => [
                [0, 255, 0],
                [0, 0, 255],
            ],
        ], JSON_THROW_ON_ERROR));


        $colors = ColorsService::extractColors($file);

        $this->assertEquals('#ff0000,#00ff00,#0000ff', $colors);
    }
}
