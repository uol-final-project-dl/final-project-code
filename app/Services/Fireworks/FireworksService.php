<?php

namespace App\Services\Fireworks;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FireworksService
{
    /**
     * @throws ConnectionException
     */
    public static function chat(array $config): array
    {
        $apiKey = config("fireworks.api_key");

        $response = Http::timeout(600)
            ->withHeaders([
                "Content-Type" => "application/json",
                "Authorization" => "Bearer {$apiKey}",
            ])->post("https://api.fireworks.ai/inference/v1/chat/completions", $config);

        $data = $response->json();

        return [$data['choices'][0]['message']['content'] ?? '', []];
    }
}
