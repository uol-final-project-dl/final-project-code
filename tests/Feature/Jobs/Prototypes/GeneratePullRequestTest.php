<?php

namespace Tests\Feature\Jobs\Prototypes;

use App\Enums\StatusEnum;
use App\Jobs\Prototypes\GeneratePullRequest;
use Mockery;
use Tests\AuthenticatedTestCase;

class GeneratePullRequestTest extends AuthenticatedTestCase
{
    public function test_handle_success(): void
    {
        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\CodeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generateCode')->andReturn(["[{\"action\":\"modify\",\"repo_path\":\"/\",\"content\":\"<html></html>\"}]", []]);

        $mockGeneration = Mockery::mock('alias:App\Services\Github\GithubRepositoriesService');
        $mockGeneration->shouldReceive('createBranch')->andReturnTrue();
        $mockGeneration->shouldReceive('updateFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createPullRequest')->andReturnTrue();

        GeneratePullRequest::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::READY->value, $this->prototype->status);
    }

    public function test_handle_invalid_response(): void
    {
        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\CodeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generateCode')->andReturn(['[{action:"modify", repo_path: "/", content: "<html></html>"}]', []]);

        $mockGeneration = Mockery::mock('alias:App\Services\Github\GithubRepositoriesService');
        $mockGeneration->shouldReceive('createBranch')->andReturnTrue();
        $mockGeneration->shouldReceive('updateFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createPullRequest')->andReturnTrue();

        GeneratePullRequest::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::FAILED->value, $this->prototype->status);
    }

    public function test_handle_need_files(): void
    {
        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\CodeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generateCode')->andReturn(["NEED_FILE", []]);

        $mockGeneration = Mockery::mock('alias:App\Services\Github\GithubRepositoriesService');
        $mockGeneration->shouldReceive('createBranch')->andReturnTrue();
        $mockGeneration->shouldReceive('updateFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createPullRequest')->andReturnTrue();

        GeneratePullRequest::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::FAILED->value, $this->prototype->status);
    }

    public function test_handle_no_files(): void
    {

        $mockGeneration = Mockery::mock('alias:App\Services\CodeGeneration\CodeGenerationWithContextService');
        $mockGeneration->shouldReceive('make')->andReturn($mockGeneration);
        $mockGeneration->shouldReceive('generateCode')->andReturn(["[]", []]);

        $mockGeneration = Mockery::mock('alias:App\Services\Github\GithubRepositoriesService');
        $mockGeneration->shouldReceive('createBranch')->andReturnTrue();
        $mockGeneration->shouldReceive('updateFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createFile')->andReturnTrue();
        $mockGeneration->shouldReceive('createPullRequest')->andReturnTrue();

        GeneratePullRequest::dispatch($this->prototype);

        $this->prototype->refresh();
        $this->assertEquals(StatusEnum::FAILED->value, $this->prototype->status);

    }
}
