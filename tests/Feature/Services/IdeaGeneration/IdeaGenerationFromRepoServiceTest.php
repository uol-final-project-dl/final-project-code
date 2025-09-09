<?php

namespace Tests\Feature\Services\IdeaGeneration;

use App\Enums\ProviderEnum;
use App\Services\IdeaGeneration\IdeaGenerationFromRepoService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class IdeaGenerationFromRepoServiceTest extends TestCase
{
    #[RunInSeparateProcess]
    public function test_generate_ideas(): void
    {
        $text = fake()->paragraph();

        $responseMock = Mockery::mock('alias:App\Services\LLM\LLMCompletionService');
        $responseMock->shouldReceive('chat')->andReturn([$text, []]);

        $response = IdeaGenerationFromRepoService::generateIdeas(ProviderEnum::OPENAI->value, 'test', 'index.html...');

        $this->assertEquals($text, $response[0]);
    }
}
