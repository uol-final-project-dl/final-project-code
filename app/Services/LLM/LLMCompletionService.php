<?php

namespace App\Services\LLM;

use App\Services\Anthropic\AnthropicCompletionService;
use App\Services\Fireworks\FireworksService;
use App\Services\Google\GoogleCompletionService;
use App\Services\Ollama\OllamaService;
use App\Services\OpenAI\OpenAICompletionsService;
use Illuminate\Http\Client\ConnectionException;

class LLMCompletionService
{
    /**
     * @throws ConnectionException
     */
    public static function chat(string $provider, array $config, array $images = []): array
    {
        return match ($provider) {
            'openai' => OpenAICompletionsService::chat(
                [
                    'model' => self::translateModelFromProvider($provider, $config['model']),
                    'messages' => $config['messages'],
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_completion_tokens' => $config['max_tokens'] ?? 3000,
                    'logprobs' => true,
                ], $images),
            'anthropic' => AnthropicCompletionService::chat([
                'model' => self::translateModelFromProvider($provider, $config['model']),
                'system' => $config['messages'][0]['content'] ?? '',
                // I pass all messages except the first one as expects the system to be set separately
                'messages' => array_slice($config['messages'], 1),
                'temperature' => $config['temperature'] ?? 0.7,
                'max_tokens' => $config['max_tokens'] ?? 3000,
            ], $images),
            'google' => GoogleCompletionService::chat([
                'model' => self::translateModelFromProvider($provider, $config['model']),
                'system_instruction' => [
                    "parts" =>
                        [
                            "text" => $config['messages'][0]['content'] ?? ''
                        ]
                ],
                "contents" => array_map(static function ($msg) {
                    return [
                        'role' => $msg['role'] ?? 'user',
                        'parts' => [
                            ['text' => $msg['content'] ?? '']
                        ]
                    ];
                }, array_slice($config['messages'], 1)),
                "generationConfig" => [
                    'temperature' => $config['temperature'] ?? 0.7,
                    //'maxOutputTokens' => $config['max_tokens'] ?? 3000,
                ],
            ], $images),
            'llama-local', 'qwen-local', 'deepseek-local' => OllamaService::chat([
                'model' => self::translateModelFromProvider($provider, $config['model']),
                'messages' => $config['messages'],
                'temperature' => $config['temperature'] ?? 0.7
            ]),
            'llama', 'qwen', 'deepseek' => FireworksService::chat(
                [
                    'model' => self::translateModelFromProvider($provider, $config['model']),
                    'messages' => $config['messages'],
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_completion_tokens' => $config['max_tokens'] ?? 3000,
                ]),
            default => throw new \InvalidArgumentException("Unsupported provider: {$provider}"),
        };
    }

    private static function translateModelFromProvider(string $provider, string $model): string
    {
        return match ($provider) {
            'openai' => match ($model) {
                'coding' => 'gpt-4.1',
                default => 'gpt-4o-mini',
            },
            'anthropic' => match ($model) {
                'coding' => 'claude-sonnet-4-20250514',
                default => 'claude-3-5-haiku-latest',
            },
            'google' => match ($model) {
                'coding' => 'gemini-2.5-pro',
                default => 'gemini-2.5-flash',
            },
            'llama-local' => match ($model) {
                'coding' => 'llama3.1:8b-instruct-q4_K_M',
                default => 'llama3.1:8b-instruct-q4_K_M',
            },
            'qwen-local' => match ($model) {
                'coding' => 'qwen2.5-coder:7b-instruct-q4_K_M',
                default => 'qwen2.5:7b-instruct-q4_K_M',
            },
            'deepseek-local' => match ($model) {
                'coding' => 'deepseek-coder-v2:16b-lite-instruct-q4_K_M',
                default => 'deepseek-v2:16b-lite-chat-q4_K_M',
            },
            'llama' => match ($model) {
                'coding' => 'accounts/fireworks/models/llama-v3p1-405b-instruct',
                default => 'accounts/fireworks/models/llama-v3p1-405b-instruct',
            },
            'qwen' => match ($model) {
                'coding' => 'accounts/fireworks/models/qwen3-coder-480b-a35b-instruct',
                default => 'accounts/fireworks/models/qwen3-235b-a22b-instruct-2507',
            },
            'deepseek' => match ($model) {
                'coding' => 'accounts/fireworks/models/deepseek-v3p1',
                default => 'accounts/fireworks/models/deepseek-v3p1',
            },
            default => throw new \InvalidArgumentException("Unsupported provider: {$provider}"),
        };
    }
}
