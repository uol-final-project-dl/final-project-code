<?php

namespace App\Services\LLM;

use App\Enums\ProviderEnum;
use App\Services\Anthropic\AnthropicCompletionService;
use App\Services\Fireworks\FireworksService;
use App\Services\Google\GoogleCompletionService;
use App\Services\OpenAI\OpenAICompletionsService;
use Illuminate\Http\Client\ConnectionException;

class LLMVisionService
{
    /**
     * @throws ConnectionException
     */
    public static function describeImage(string $provider, string $imagePath)
    {
        $base64 = base64_encode(file_get_contents($imagePath));
        $mime = mime_content_type($imagePath);

        $prompt = <<<PROMPT
        Convert this screenshot into semantic HTML pseudo-code.
        This will be fed into another LLM for code generation so it must be simple.
        If you can’t detect something, use a short placeholder.
        PROMPT;

        $config = [
            'temperature' => 0.3,
            'max_tokens' => 8000
        ];

        return self::prompt($provider, $config, $base64, $prompt, $mime);
    }

    /**
     * @throws ConnectionException
     */
    public static function prompt(string $provider, array $config, string $base64, string $prompt, string $mimeType): string
    {
        return match ($provider) {
            ProviderEnum::ANTHROPIC->value => AnthropicCompletionService::chat([
                'model' => self::translateModelFromProvider($provider),
                'temperature' => $config['temperature'] ?? 0.7,
                'max_tokens' => $config['max_tokens'] ?? 3000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt
                            ],
                            [
                                'type' => 'image',
                                'source' => [
                                    "type" => "base64",
                                    "media_type" => $mimeType,
                                    "data" => $base64
                                ]
                            ]
                        ]
                    ],
                ],
            ])[0],
            ProviderEnum::GOOGLE->value => GoogleCompletionService::chat([
                'model' => self::translateModelFromProvider($provider),
                "contents" => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                "text" => $prompt
                            ],
                            ['inline_data' =>
                                [
                                    'mime_type' => $mimeType,
                                    'data' => $base64
                                ]
                            ]
                        ]
                    ]
                ],
                "generationConfig" => [
                    'temperature' => $config['temperature'] ?? 0.7,
                ],
            ])[0],
            ProviderEnum::QWEN->value => FireworksService::chat(
                [
                    'model' => self::translateModelFromProvider($provider),
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_completion_tokens' => $config['max_tokens'] ?? 3000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $prompt
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => 'data:' . $mimeType . ';base64,' . $base64]
                                ]
                            ]
                        ],
                    ],
                ])[0],
            default => OpenAICompletionsService::chat(
                [
                    'model' => self::translateModelFromProvider($provider),
                    'temperature' => $config['temperature'] ?? 0.7,
                    'max_completion_tokens' => $config['max_tokens'] ?? 3000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $prompt
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => 'data:' . $mimeType . ';base64,' . $base64]
                                ]
                            ]
                        ],
                    ],
                ])[0],
        };
    }

    private static function translateModelFromProvider(string $provider): string
    {
        return match ($provider) {
            ProviderEnum::ANTHROPIC->value => 'claude-sonnet-4-20250514',
            ProviderEnum::GOOGLE->value => 'gemini-2.5-pro',
            ProviderEnum::QWEN->value => 'accounts/fireworks/models/qwen3-235b-a22b-instruct-2507',
            default => 'gpt-4.1',
        };
    }
}
