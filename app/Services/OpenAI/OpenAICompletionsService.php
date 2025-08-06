<?php

namespace App\Services\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAICompletionsService
{
    public static function chat(array $config): string
    {
        $resp = OpenAI::chat()->create($config);

        return $resp['choices'][0]['message']['content'] ?? '';
    }
}
