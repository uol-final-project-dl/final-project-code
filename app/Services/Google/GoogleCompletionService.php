<?php

namespace App\Services\Google;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GoogleCompletionService
{
    /**
     * @throws ConnectionException
     */
    public static function chat(array $config): string
    {
        $apiKey = config('google.api_key');

        $response = Http::timeout(600)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])->post(
                'https://generativelanguage.googleapis.com/v1beta/models/' . $config['model'] . ':generateContent',
                // remove  model from config array
                array_diff_key($config, ['model' => ''])
            );

        $data = $response->json();

        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
