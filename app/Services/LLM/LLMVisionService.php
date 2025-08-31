<?php

namespace App\Services\LLM;

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
            'anthropic' => AnthropicCompletionService::chat([
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
            ]),
            'google' => GoogleCompletionService::chat([
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
            ]),
            'qwen' => FireworksService::chat(
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
                ]),
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
                ]),
        };
    }

    private static function translateModelFromProvider(string $provider): string
    {
        return match ($provider) {
            'anthropic' => 'claude-sonnet-4-20250514',
            'google' => 'gemini-2.5-pro',
            'qwen' => 'accounts/fireworks/models/qwen3-235b-a22b-instruct-2507',
            default => 'gpt-4.1',
        };
    }
}
