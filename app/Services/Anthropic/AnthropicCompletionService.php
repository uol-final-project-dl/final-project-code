<?php

namespace App\Services\Anthropic;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AnthropicCompletionService
{
    /**
     * @throws ConnectionException
     */
    public static function chat(array $config): string
    {
        $apiKey = config('anthropic.api_key');

        $response = Http::timeout(600)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
                'x-api-key' => $apiKey,
            ])->post('https://api.anthropic.com/v1/messages', $config);

        $data = $response->json();

// Safely return the text or ''
        return $data['content'][0]['text'] ?? '';

    }
}
