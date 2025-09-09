<?php

namespace Tests\Feature\Services\LLM;

use App\Enums\ProviderEnum;
use App\Services\LLM\LLMCompletionService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class LLMCompletionServiceTest extends AuthenticatedTestCase
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

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::ANTHROPIC->value, [
            'model' => 'ideating',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_anthropic_coding(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Anthropic\AnthropicCompletionService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::ANTHROPIC->value, [
            'model' => 'coding',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
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

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::LLAMA->value, [
            'model' => 'ideating',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_opensource_coding(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Fireworks\FireworksService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::LLAMA->value, [
            'model' => 'coding',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_opensource_local_ideating(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Ollama\OllamaService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::LLAMA_LOCAL->value, [
            'model' => 'ideating',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_opensource_local_coding(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Ollama\OllamaService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::LLAMA_LOCAL->value, [
            'model' => 'coding',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
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

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::GOOGLE->value, [
            'model' => 'ideating',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_google_coding(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\Google\GoogleCompletionService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::GOOGLE->value, [
            'model' => 'coding',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
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

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::OPENAI->value, [
            'model' => 'ideating',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
    }

    /**
     * @return void
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    #[RunInSeparateProcess]
    public function test_chat_openai_coding(): void
    {
        $text = fake()->paragraph();

        $mockSearch = Mockery::mock('alias:App\Services\OpenAI\OpenAICompletionsService');
        $mockSearch->shouldReceive('chat')->andReturn([$text, []]);

        [$response, $logprobs] = LLMCompletionService::chat(ProviderEnum::OPENAI->value, [
            'model' => 'coding',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => '<html></html>'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        $this->assertEquals($response, $text);
        $this->assertEquals($logprobs, []);
    }
}
