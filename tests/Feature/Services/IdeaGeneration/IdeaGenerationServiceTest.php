<?php

namespace Tests\Feature\Services\IdeaGeneration;

use App\Enums\ProviderEnum;
use App\Services\IdeaGeneration\IdeaGenerationService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class IdeaGenerationServiceTest extends TestCase
{
    #[RunInSeparateProcess]
    public function test_generate_ideas(): void
    {
        $text = fake()->paragraph();

        $responseMock = Mockery::mock('alias:App\Services\LLM\LLMCompletionService');
        $responseMock->shouldReceive('chat')->andReturn([$text, []]);

        $response = IdeaGenerationService::generateIdeas(ProviderEnum::OPENAI->value, 'test');

        $this->assertEquals($text, $response[0]);
    }
}
