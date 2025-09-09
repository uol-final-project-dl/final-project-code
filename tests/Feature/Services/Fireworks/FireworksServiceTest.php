<?php

namespace Tests\Feature\Services\Fireworks;

use App\Services\Fireworks\FireworksService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class FireworksServiceTest extends TestCase
{
    public function test_chat(): void
    {
        $text = fake()->paragraph();
        $responseMock = Mockery::mock('alias:Illuminate\Http\Client\Response');
        $responseMock->shouldReceive('json')->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => $text
                    ]
                ]
            ]
        ]);

        Http::fake();
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($responseMock);

        $chatResponse = FireworksService::chat([]);

        $this->assertEquals($text, $chatResponse[0]);
    }
}
