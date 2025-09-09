<?php

namespace Tests\Feature\Services\OpenAI;

use App\Services\OpenAI\OpenAIEmbeddingsService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class OpenAIEmbeddingsServiceTest extends TestCase
{
    #[RunInSeparateProcess]
    public function test_embed(): void
    {
        $text = fake()->paragraph();
        $vectorString = implode(',', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

        $openAIMock = Mockery::mock('alias:OpenAI\Laravel\Facades\OpenAI');
        $openAIMock->shouldReceive('embeddings')->andReturnSelf();
        $openAIMock->shouldReceive('create')->andReturn([
            'data' => [
                [
                    'embedding' => explode(',', $vectorString)
                ]
            ]
        ]);

        $chatResponse = OpenAIEmbeddingsService::embed($text);

        $this->assertEquals($vectorString, implode(',', $chatResponse));
    }
}
