<?php

namespace Tests\Feature\Services\Anthropic;

use App\Services\Anthropic\AnthropicCompletionService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class AnthropicCompletionServiceTest extends TestCase
{
    public function test_chat(): void
    {
        $text = fake()->paragraph();
        $responseMock = Mockery::mock('alias:Illuminate\Http\Client\Response');
        $responseMock->shouldReceive('json')->andReturn([
            'content' => [
                [
                    'type' => 'text',
                    'text' => $text
                ]
            ]
        ]);

        Http::fake();
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($responseMock);

        $chatResponse = AnthropicCompletionService::chat([]);

        $this->assertEquals($text, $chatResponse[0]);
    }

    public function test_chat_with_images(): void
    {
        $text = fake()->paragraph();
        $responseMock = Mockery::mock('alias:Illuminate\Http\Client\Response');
        $responseMock->shouldReceive('json')->andReturn([
            'content' => [
                [
                    'type' => 'text',
                    'text' => $text
                ]
            ]
        ]);

        Http::fake();
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($responseMock);

        $chatResponse = AnthropicCompletionService::chat([], [
            [
                'mimeType' => 'image/png',
                'base64' => base64_encode('sample')
            ]
        ]);

        $this->assertEquals($text, $chatResponse[0]);
    }
}
