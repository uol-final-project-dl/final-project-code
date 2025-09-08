<?php

namespace Tests\Feature\Services\CodeGeneration;

use App\Services\CodeGeneration\PrototypeGenerationWithContextService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class PrototypeGenerationWithContextServiceTest extends AuthenticatedTestCase
{
    /**
     * @throws \Exception
     */
    #[RunInSeparateProcess]
    public function test_generate(): void
    {
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

        [$response, $references] = PrototypeGenerationWithContextService::generate($this->prototype,
            'Generate a simple HTML file',
            'test',
            'test',
            'test',
            true
        );

        $this->assertStringContainsString('<html></html>', $response);
        $this->assertCount(0, $references);
    }
}
