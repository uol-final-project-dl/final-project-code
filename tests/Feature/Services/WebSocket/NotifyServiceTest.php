<?php

namespace Tests\Feature\Services\WebSocket;

use App\Services\WebSocket\NotifyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Pusher\ApiErrorException;
use Pusher\PusherException;
use Tests\TestCase;

class NotifyServiceTest extends TestCase
{
    /**
     * @throws PusherException
     * @throws GuzzleException
     * @throws ApiErrorException
     * @throws BindingResolutionException
     */
    #[RunInSeparateProcess]
    public function test_reload_user_page(): void
    {
        $userId = 1;
        $mockPusherService = Mockery::mock('alias:App\Services\WebSocket\PusherService');
        $mockPusherService->shouldReceive('make')->andReturnSelf();
        $mockPusherService->shouldReceive('trigger')->andReturn((object)['status' => 'ok']);

        $statusObject = NotifyService::reloadUserPage($userId);
        $this->assertEquals('ok', $statusObject->status);
    }
}
