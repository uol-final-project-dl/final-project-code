<?php

namespace App\Services\Ollama;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    public static function chat(array $config = []): array
    {
        $baseUrl = config('ollama.base_url');
        $payload = [
            'model' => $config['model'] ?? 'llama3.1:8b-instruct-q4_K_M',
            'messages' => $config['messages'] ?? [],
            'stream' => false,
        ];

        $payload['options'] = [
            'temperature' => $config['temperature'] ?? null,
            'num_ctx' => 20000,
        ];

        try {
            $res = Http::timeout(10000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}/api/chat", $payload);

            $data = $res->json();
            return [$data['message']['content'] ?? '', []];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['', []];
        }
    }
}
