<?php

namespace App\Services\WebSocket;

use App\Traits\HasMakeAble;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Pusher\ApiErrorException;
use Pusher\Pusher;
use Pusher\PusherException;

class PusherService
{
    use HasMakeAble;

    private Pusher $pusher;

    public function __construct()
    {
        $key = Config::get('broadcasting.connections.pusher.key');
        $secret = Config::get('broadcasting.connections.pusher.secret');
        $app_id = Config::get('broadcasting.connections.pusher.app_id');
        $options = Config::get('broadcasting.connections.pusher.options');
        try {
            $this->pusher = new Pusher($key, $secret, $app_id, $options);
        } catch (PusherException $e) {
            Log::info($e->getMessage());
        }
    }

    /**
     * @throws PusherException
     * @throws ApiErrorException
     * @throws GuzzleException
     */
    public function trigger($room, $event, $data): object
    {
        return $this->pusher->trigger($room, $event, $data);
    }

    public function socket_auth($channel, $id): string
    {
        return $this->pusher->socket_auth($channel, $id);
    }
}

