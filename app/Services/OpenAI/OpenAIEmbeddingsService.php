<?php

namespace App\Services\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAIEmbeddingsService
{
    public static function embed(string $text): array
    {
        $resp = OpenAI::embeddings()->create([
            'model' => 'text-embedding-3-small',
            'dimensions' => 1024,
            'input' => $text,
        ]);

        return $resp['data'][0]['embedding'];
    }
}
