<?php

namespace App\Services\LLM;

use App\Services\Anthropic\AnthropicCompletionService;
use App\Services\Google\GoogleCompletionService;
use App\Services\OpenAI\OpenAICompletionsService;

class LLMCompletionService
{
    public static function chat(string $provider, array $config): string
    {
        switch ($provider) {
            case 'openai':
                return OpenAICompletionsService::chat(
                    [
                        'model' => self::translateModelFromProvider($provider, $config['model']),
                        'messages' => $config['messages'],
                        'temperature' => $config['temperature'] ?? 0.7,
                        'max_completion_tokens' => $config['max_tokens'] ?? 3000,
                    ]);
            case 'anthropic':
                return AnthropicCompletionService::chat([
                    'model' => self::translateModelFromProvider($provider, $config['model']),
                    'system' => $config['messages'][0]['content'] ?? '',
                    // I pass all messages except the first one as expects the system to be set separately
                    'messages' => array_slice($config['messages'], 1),
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_tokens' => $config['max_tokens'] ?? 3000,
                ]);
            case 'google':
                return GoogleCompletionService::chat([
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
                ]);
            default:
                throw new \InvalidArgumentException("Unsupported provider: {$provider}");
        }
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
            default => throw new \InvalidArgumentException("Unsupported provider: {$provider}"),
        };
    }
}
