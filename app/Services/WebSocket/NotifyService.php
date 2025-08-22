<?php

namespace App\Services\WebSocket;

use App\Traits\HasMakeAble;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Pusher\ApiErrorException;
use Pusher\PusherException;

class NotifyService
{
    use HasMakeAble;

    /**
     * @throws PusherException
     * @throws ApiErrorException
     * @throws GuzzleException
     * @throws BindingResolutionException
     */
    public static function reloadUserPage(int $userId): object
    {
        return PusherService::make()->trigger('private-user-channel-' . $userId, 'reload-event', []);
    }


}

