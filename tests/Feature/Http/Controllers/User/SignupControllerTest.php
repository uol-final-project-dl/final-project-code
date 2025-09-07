<?php

namespace Tests\Feature\Http\Controllers\User;

use Tests\TestCase;

class SignupControllerTest extends TestCase
{
    public function test_post_signup(): void
    {
        $password = fake()->password;
        $postData = [
            'name' => fake()->name,
            'email' => fake()->unique()->safeEmail,
            'password' => $password,
            'password_confirmation' => $password
        ];

        $response = $this->post(
            '/api/user/postSignup',
            $postData
        );

        $response->assertStatus(200);
        $response->assertJson([
            'result' => 1,
        ]);
        $this->assertDatabaseHas('users',
            [
                'email' => $postData['email'],
            ]
        );
    }
}
