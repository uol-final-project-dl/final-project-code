<?php

namespace Tests\Feature\Services\OpenAI;

use App\Services\OpenAI\OpenAICompletionsService;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class OpenAICompletionsServiceTest extends TestCase
{
    #[RunInSeparateProcess]
    public function test_chat(): void
    {
        $text = fake()->paragraph();

        $openAIMock = Mockery::mock('alias:OpenAI\Laravel\Facades\OpenAI');
        $openAIMock->shouldReceive('chat')->andReturnSelf();
        $openAIMock->shouldReceive('create')->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => $text
                    ]
                ]
            ]
        ]);

        $chatResponse = OpenAICompletionsService::chat([], [
            [
                'mimeType' => 'image/png',
                'base64' => base64_encode('sample')
            ]
        ]);

        $this->assertEquals($text, $chatResponse[0]);
    }
}
