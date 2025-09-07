<?php

namespace Tests\Feature\Http\Controllers\User;

use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    public function test_post_login(): void
    {
        $response = $this->post(
            '/api/user/postLogin',
            [
                'email' => $this->user->email,
                'password' => 'password',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'result' => 1,
        ]);
    }

    public function test_logout(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/user/logout');
        $response->assertStatus(302);
        $response->assertRedirect(route('users.home'));
    }
}
