<?php

namespace App\Services\Google;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GoogleCompletionService
{
    /**
     * @throws ConnectionException
     */
    public static function chat(array $config, array $images = []): array
    {
        $apiKey = config('google.api_key');

        if (is_array($images) && !empty($images)) {
            foreach ($images as $image) {
                $config['contents'][] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            "text" => 'Use this screenshot as the reference structure'
                        ],
                        ['inline_data' =>
                            [
                                'mime_type' => $image['mimeType'],
                                'data' => $image['base64']
                            ]
                        ]
                    ]
                ];
            }
        }

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

        return [$data['candidates'][0]['content']['parts'][0]['text'] ?? '', self::convertLogprobArrayIntoOpenAIFormat($data['candidates'][0]['logprobsResult']['chosenCandidates'] ?? [])];
    }

    private static function convertLogprobArrayIntoOpenAIFormat(array $logprobs): array
    {
        $result = [];

        foreach ($logprobs as $item) {
            $result[] = [
                'logprob' => $item['logProbability'] ?? -9999.0,
            ];
        }

        return $result;
    }
}
