<?php

namespace Tests\Feature\Services\Ollama;

use App\Services\Ollama\OllamaService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class OllamaServiceTest extends TestCase
{
    public function test_chat(): void
    {
        $text = fake()->paragraph();
        $responseMock = Mockery::mock('alias:Illuminate\Http\Client\Response');
        $responseMock->shouldReceive('json')->andReturn([
            'message' => [
                'content' => $text
            ]
        ]);

        Http::fake();
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($responseMock);

        $chatResponse = OllamaService::chat([
            'model' => 'llama3.1',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hello, how are you?'
                ]
            ],
            'temperature' => 0.7
        ]);

        $this->assertEquals($text, $chatResponse[0]);
    }
}
