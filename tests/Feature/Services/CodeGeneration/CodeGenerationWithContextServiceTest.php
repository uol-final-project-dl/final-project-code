<?php

namespace Tests\Feature\Services\CodeGeneration;

use App\Enums\ProviderEnum;
use App\Services\CodeGeneration\CodeGenerationWithContextService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class CodeGenerationWithContextServiceTest extends AuthenticatedTestCase
{

    /**
     * @throws \JsonException
     */
    #[RunInSeparateProcess]
    public function test_generate_code(): void
    {
        $mockSearch = Mockery::mock('alias:App\Services\VectorDB\SearchVectorDBService');
        $mockSearch->shouldReceive('searchFileChunks')->andReturn(collect([]));

        $mockImage = Mockery::mock('alias:App\Services\FileParsing\ImageBase64Service');
        $mockImage->shouldReceive('base64DocumentsFromProject')->andReturn([[
            'base64' => base64_encode('sample'),
            'mimeType' => 'image/png',
        ]]);

        $mockLLM = Mockery::mock('alias:App\Services\LLM\LLMCompletionService');
        $mockLLM->shouldReceive('chat')->andReturn([
            '<html></html>',
            []
        ]);

        [$response, $references] = CodeGenerationWithContextService::generateCode($this->project, ProviderEnum::OPENAI->value, 'Generate a simple HTML file');

        $this->assertStringContainsString('<html></html>', $response);
        $this->assertCount(0, $references);
    }
}
