<?php

namespace Tests\Feature\Http\Controllers\User;

use Mockery;
use Tests\AuthenticatedTestCase;

class MainControllerTest extends AuthenticatedTestCase
{
    public function test_index(): void
    {
        $response = $this->get(
            '/user/app/'
        );

        $response->assertStatus(200);
        $response->assertViewIs('user');
    }

    public function test_get_data(): void
    {
        $mock = Mockery::mock('alias:App\Services\Github\GithubRepositoriesService');
        $mock->shouldReceive('getAllRepositories')->once()->andReturn([]);

        $response = $this->get(
            '/api/getData'
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'result',
            'githubRepositories',
            'userId',
        ]);
    }

    public function test_pusher_auth(): void
    {
        $mock = Mockery::mock('alias:App\Services\WebSocket\PusherService');
        $mock->shouldReceive('socket_auth')->once()->andReturn('auth_response');
        $mock->shouldReceive('make')->once()->andReturn($mock);

        $response = $this->post(
            '/user/pusher/auth',
            [
                'socket_id' => fake()->uuid,
            ]
        );

        $response->assertStatus(200);
    }
}
