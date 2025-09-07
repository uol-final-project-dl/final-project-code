<?php

namespace App\Services\Anthropic;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AnthropicCompletionService
{
    /**
     * @throws ConnectionException
     */
    public static function chat(array $config, array $images = []): array
    {
        $apiKey = config('anthropic.api_key');

        if (is_array($images) && !empty($images)) {
            foreach ($images as $image) {
                $config['messages'][] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Use this screenshot as the reference structure'
                        ],
                        [
                            'type' => 'image',
                            'source' => [
                                "type" => "base64",
                                "media_type" => $image['mimeType'],
                                "data" => $image['base64']
                            ]
                        ]
                    ]
                ];
            }
        }

        $response = Http::timeout(600)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' => $apiKey,
            ])->post('https://api.anthropic.com/v1/messages', $config);

        $data = $response->json();

        return [$data['content'][0]['text'] ?? '', []];
    }
}
