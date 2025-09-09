<?php

namespace Tests\Feature\Services\LLM;

use App\Enums\ProviderEnum;
use App\Services\LLM\LLMVisionService;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class LLMVisionServiceTest extends AuthenticatedTestCase
{

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_anthropic_ideating(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Anthropic\AnthropicCompletionService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        $imagePath = storage_path('app/tmp/' . Str::uuid() . '_test_file.png');
        file_put_contents($imagePath, 'Hello, World!');

        $response = LLMVisionService::describeImage(ProviderEnum::ANTHROPIC->value, $imagePath);

        $this->assertEquals($response, $text);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_opensource_ideating(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Fireworks\FireworksService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        $imagePath = storage_path('app/tmp/' . Str::uuid() . '_test_file.png');
        file_put_contents($imagePath, 'Hello, World!');
        $response = LLMVisionService::describeImage(ProviderEnum::QWEN->value, $imagePath);

        $this->assertEquals($response, $text);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_google_ideating(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Google\GoogleCompletionService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        $imagePath = storage_path('app/tmp/' . Str::uuid() . '_test_file.png');
        file_put_contents($imagePath, 'Hello, World!');
        $response = LLMVisionService::describeImage(ProviderEnum::GOOGLE->value, $imagePath);

        $this->assertEquals($response, $text);
    }


    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_openai_ideating(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\OpenAI\OpenAICompletionsService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        $imagePath = storage_path('app/tmp/' . Str::uuid() . '_test_file.png');
        file_put_contents($imagePath, 'Hello, World!');
        $response = LLMVisionService::describeImage(ProviderEnum::OPENAI->value, $imagePath);

        $this->assertEquals($response, $text);
    }
}
