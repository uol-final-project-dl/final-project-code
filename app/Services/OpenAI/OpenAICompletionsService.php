<?php

namespace App\Services\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAICompletionsService
{
    public static function chat(array $config, array $images = []): string
    {
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
                            'type' => 'image_url',
                            'image_url' => ['url' => 'data:' . $image['mimeType'] . ';base64,' . $image['base64']]
                        ]
                    ]
                ];
            }
        }


        $resp = OpenAI::chat()->create($config);

        return $resp['choices'][0]['message']['content'] ?? '';
    }
}
