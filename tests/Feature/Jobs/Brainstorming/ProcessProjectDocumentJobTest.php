<?php

namespace Tests\Feature\Jobs\Brainstorming;

use App\Enums\StatusEnum;
use App\Jobs\Brainstorming\ProcessProjectDocumentJob;
use App\Models\ProjectDocument;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\AuthenticatedTestCase;

class ProcessProjectDocumentJobTest extends AuthenticatedTestCase
{
    public function test_handle_file_not_found(): void
    {
        $projectDocument = ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'filename' => 'example.txt',
            'type' => 'text',
            'status' => StatusEnum::QUEUED->value,
            'content' => null,
        ]);

        ProcessProjectDocumentJob::dispatch($projectDocument);

        $projectDocument->refresh();

        $this->assertEquals(StatusEnum::FAILED->value, $projectDocument->status);
    }

    public function test_handle_text(): void
    {
        Config::set('filesystems.default', 'local');

        $fileContent = fake()->paragraphs(3, true);

        Storage::fake('local');
        Storage::put('example.txt', $fileContent);

        $projectDocument = ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'filename' => 'example.txt',
            'type' => 'text/plain',
            'status' => StatusEnum::QUEUED->value,
            'content' => null,
        ]);

        ProcessProjectDocumentJob::dispatch($projectDocument);

        $projectDocument->refresh();

        $this->assertEquals(StatusEnum::READY->value, $projectDocument->status);
        $this->assertEquals($fileContent, $projectDocument->content);
    }

    public function test_handle_pdf(): void
    {
        Config::set('filesystems.default', 'local');

        Storage::fake('local');
        Storage::put('example.pdf', file_get_contents(__DIR__ . '/../../../Resources/TestFiles/sample.pdf'));

        $projectDocument = ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'filename' => 'example.pdf',
            'type' => 'application/pdf',
            'status' => StatusEnum::QUEUED->value,
            'content' => null,
        ]);

        ProcessProjectDocumentJob::dispatch($projectDocument);

        $projectDocument->refresh();

        $this->assertEquals(StatusEnum::READY->value, $projectDocument->status);
        $this->assertEquals('Sample text', $projectDocument->content);
    }

    public function test_handle_audio(): void
    {
        Config::set('filesystems.default', 'local');

        Storage::fake('local');
        Storage::put('example.mp3', file_get_contents(__DIR__ . '/../../../Resources/TestFiles/sample.pdf'));

        $projectDocument = ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'filename' => 'example.mp3',
            'type' => 'audio/mpeg',
            'status' => StatusEnum::QUEUED->value,
            'content' => null,
        ]);

        $mockTrim = Mockery::mock('alias:App\Services\FFMPEG\FFMPEGService');
        $mockTrim->shouldReceive('trim')->once()->andReturn(null);

        $mockTranscribe = Mockery::mock('alias:App\Services\Whisper\WhisperService');
        $mockTranscribe->shouldReceive('transcribe')->once()->andReturn('Sample text');

        ProcessProjectDocumentJob::dispatch($projectDocument);

        $projectDocument->refresh();

        $this->assertEquals(StatusEnum::READY->value, $projectDocument->status);
        $this->assertEquals('Sample text', $projectDocument->content);
    }

    public function test_handle_video(): void
    {
        Config::set('filesystems.default', 'local');

        Storage::fake('local');
        Storage::put('example.mp4', file_get_contents(__DIR__ . '/../../../Resources/TestFiles/sample.pdf'));

        $projectDocument = ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'filename' => 'example.mp4',
            'type' => 'video/mp4',
            'status' => StatusEnum::QUEUED->value,
            'content' => null,
        ]);

        $processMock = Mockery::mock('alias:' . Process::class);
        $processMock->shouldReceive('run')->once()->andReturn(1);
        $processMock->shouldReceive('isSuccessful')->once()->andReturnTrue();

        $mockTrim = Mockery::mock('alias:App\Services\FFMPEG\FFMPEGService');
        $mockTrim->shouldReceive('trim')->once()->andReturn(null);
        $mockTrim->shouldReceive('getProcess')->once()->andReturn($processMock);

        $mockTranscribe = Mockery::mock('alias:App\Services\Whisper\WhisperService');
        $mockTranscribe->shouldReceive('transcribe')->once()->andReturn('Sample text');

        ProcessProjectDocumentJob::dispatch($projectDocument);

        $projectDocument->refresh();

        $this->assertEquals(StatusEnum::READY->value, $projectDocument->status);
        $this->assertEquals('Sample text', $projectDocument->content);
    }

    public function test_handle_image(): void
    {
        Config::set('filesystems.default', 'local');

        Storage::fake('local');
        Storage::put('example.png', file_get_contents(__DIR__ . '/../../../Resources/TestFiles/sample.pdf'));

        $projectDocument = ProjectDocument::factory()->create([
            'project_id' => $this->project->id,
            'filename' => 'example.png',
            'type' => 'image/png',
            'status' => StatusEnum::QUEUED->value,
            'content' => null,
        ]);

        $mockVision = Mockery::mock('alias:App\Services\LLM\LLMVisionService');
        $mockVision->shouldReceive('describeImage')->once()->andReturn('Sample image');

        $mockColors = Mockery::mock('alias:App\Services\PythonServices\ColorsService');
        $mockColors->shouldReceive('extractColors')->once()->andReturn('Sample colors');

        ProcessProjectDocumentJob::dispatch($projectDocument);

        $projectDocument->refresh();

        $this->assertEquals(StatusEnum::READY->value, $projectDocument->status);
        $this->assertEquals('Sample image', $projectDocument->content);

        $this->project->refresh();
        $this->assertStringContainsString('Sample colors', $this->project->style_config);
    }
}
