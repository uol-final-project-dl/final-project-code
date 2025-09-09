<?php

namespace Tests\Feature\Services\Google;

use App\Services\Google\GoogleCompletionService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class GoogleCompletionServiceTest extends TestCase
{
    public function test_chat(): void
    {
        $text = fake()->paragraph();
        $responseMock = Mockery::mock('alias:Illuminate\Http\Client\Response');
        $responseMock->shouldReceive('json')->andReturn([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => $text
                            ]
                        ]
                    ],
                    'logprobsResult' => [
                        'chosenCandidates' => [
                            ['logProbability' => -0.1]
                        ]
                    ]
                ]
            ]
        ]);

        Http::fake();
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($responseMock);

        $chatResponse = GoogleCompletionService::chat([
            'model' => 'models/gemini-2.5-pro',
        ]);

        $this->assertEquals($text, $chatResponse[0]);
    }

    public function test_chat_with_images(): void
    {
        $text = fake()->paragraph();
        $responseMock = Mockery::mock('alias:Illuminate\Http\Client\Response');
        $responseMock->shouldReceive('json')->andReturn([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => $text
                            ]
                        ]
                    ],
                    'logprobsResult' => [
                        'chosenCandidates' => [
                            ['logProbability' => -0.1]
                        ]
                    ]
                ]
            ]
        ]);

        Http::fake();
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('withHeaders')->andReturnSelf();
        Http::shouldReceive('post')->andReturn($responseMock);

        $chatResponse = GoogleCompletionService::chat([
            'model' => 'models/gemini-2.5-pro',
        ], [
            [
                'mimeType' => 'image/png',
                'base64' => base64_encode('sample')
            ]
        ]);

        $this->assertEquals($text, $chatResponse[0]);
    }
}
