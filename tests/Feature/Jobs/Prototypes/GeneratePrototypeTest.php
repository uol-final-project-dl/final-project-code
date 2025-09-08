<?php

namespace Tests\Feature\Jobs\Prototypes;

use App\Enums\StatusEnum;
use App\Jobs\Prototypes\GeneratePrototype;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\AuthenticatedTestCase;

class GeneratePrototypeTest extends AuthenticatedTestCase
{
    public function test_handle_success(): void
    {
        $mockProcessResult = Mockery::mock('alias:Illuminate\Contracts\Process\ProcessResult');
        $mockProcessResult->shouldReceive('failed')->andReturn(false);
        $mockProcessResult->shouldReceive('errorOutput')->andReturn("No error");
        $mockProcessResult->shouldReceive('output')->andReturn("Process output");

        $processMock = Mockery::mock('alias:' . Process::class);
        $processMock->shouldReceive('run')->andReturn($mockProcessResult);
        $processMock->shouldReceive('isSuccessful')->andReturnTrue();
        $processMock->shouldReceive('path')->andReturnSelf();

        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\PrototypeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generate')->andReturn(["<html></html>", []]);

        Storage::fake();
        Storage::shouldReceive('disk')->with('minio')->andReturnSelf();
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('putFile')->andReturn('some/path/to/file.png');
        Storage::shouldReceive('put')->andReturnTrue();
        Storage::shouldReceive('makeDirectory')->andReturnTrue();

        GeneratePrototype::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::READY->value, $this->prototype->status);
    }

    public function test_handle_log_error(): void
    {
        $this->prototype->update([
            'log' => 'Initial  log entry.'
        ]);

        $mockProcessResult = Mockery::mock('alias:Illuminate\Contracts\Process\ProcessResult');
        $mockProcessResult->shouldReceive('failed')->andReturn(false);
        $mockProcessResult->shouldReceive('errorOutput')->andReturn("No error");
        $mockProcessResult->shouldReceive('output')->andReturn("Process output");

        $processMock = Mockery::mock('alias:' . Process::class);
        $processMock->shouldReceive('run')->andReturn($mockProcessResult);
        $processMock->shouldReceive('isSuccessful')->andReturnTrue();
        $processMock->shouldReceive('path')->andReturnSelf();

        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\PrototypeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generate')->andReturn(["<html></html>", []]);

        Storage::fake();
        Storage::shouldReceive('disk')->with('minio')->andReturnSelf();
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('putFile')->andReturn('some/path/to/file.png');
        Storage::shouldReceive('put')->andReturnTrue();
        Storage::shouldReceive('makeDirectory')->andReturnTrue();

        GeneratePrototype::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::READY->value, $this->prototype->status);
    }

    public function test_handle_failed_incomplete(): void
    {
        $this->prototype->update([
            'log' => 'Initial  log entry.'
        ]);

        $mockProcessResult = Mockery::mock('alias:Illuminate\Contracts\Process\ProcessResult');
        $mockProcessResult->shouldReceive('failed')->andReturn(true);
        $mockProcessResult->shouldReceive('errorOutput')->andReturn("Unexpected end of file");
        $mockProcessResult->shouldReceive('output')->andReturn("Process output");

        $processMock = Mockery::mock('alias:' . Process::class);
        $processMock->shouldReceive('run')->andReturn($mockProcessResult);
        $processMock->shouldReceive('isSuccessful')->andReturnTrue();
        $processMock->shouldReceive('path')->andReturnSelf();

        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\PrototypeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generate')->andReturn(["<html></html>", []]);

        Storage::fake();
        Storage::shouldReceive('disk')->with('minio')->andReturnSelf();
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('putFile')->andReturn('some/path/to/file.png');
        Storage::shouldReceive('put')->andReturnTrue();
        Storage::shouldReceive('makeDirectory')->andReturnTrue();

        GeneratePrototype::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::FAILED->value, $this->prototype->status);
    }

    public function test_handle_failed_complete(): void
    {
        $this->prototype->update([
            'log' => 'Initial  log entry.'
        ]);

        $mockProcessResult = Mockery::mock('alias:Illuminate\Contracts\Process\ProcessResult');
        $mockProcessResult->shouldReceive('failed')->andReturn(true);
        $mockProcessResult->shouldReceive('errorOutput')->andReturn("A different error");
        $mockProcessResult->shouldReceive('output')->andReturn("Process output");

        $processMock = Mockery::mock('alias:' . Process::class);
        $processMock->shouldReceive('run')->andReturn($mockProcessResult);
        $processMock->shouldReceive('isSuccessful')->andReturnTrue();
        $processMock->shouldReceive('path')->andReturnSelf();

        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\PrototypeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generate')->andReturn(["<html></html>", []]);

        Storage::fake();
        Storage::shouldReceive('disk')->with('minio')->andReturnSelf();
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('putFile')->andReturn('some/path/to/file.png');
        Storage::shouldReceive('put')->andReturnTrue();
        Storage::shouldReceive('makeDirectory')->andReturnTrue();

        GeneratePrototype::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::FAILED->value, $this->prototype->status);
    }

    public function test_handle_remix(): void
    {
        $mockProcessResult = Mockery::mock('alias:Illuminate\Contracts\Process\ProcessResult');
        $mockProcessResult->shouldReceive('failed')->andReturn(false);
        $mockProcessResult->shouldReceive('errorOutput')->andReturn("No error");
        $mockProcessResult->shouldReceive('output')->andReturn("Process output");

        $processMock = Mockery::mock('alias:' . Process::class);
        $processMock->shouldReceive('run')->andReturn($mockProcessResult);
        $processMock->shouldReceive('isSuccessful')->andReturnTrue();
        $processMock->shouldReceive('path')->andReturnSelf();

        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\PrototypeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generate')->andReturn(["<html></html>", []]);

        Storage::fake();
        Storage::shouldReceive('disk')->with('minio')->andReturnSelf();
        Storage::shouldReceive('disk')->with('local')->andReturnSelf();
        Storage::shouldReceive('putFile')->andReturn('some/path/to/file.png');
        Storage::shouldReceive('put')->andReturnTrue();
        Storage::shouldReceive('makeDirectory')->andReturnTrue();
        Storage::shouldReceive('get')->andReturn("<html></html>");

        GeneratePrototype::dispatch($this->prototype, true, "Make it better");

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::READY->value, $this->prototype->status);
    }
}
