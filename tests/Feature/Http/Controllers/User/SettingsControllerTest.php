<?php

namespace Tests\Feature\Http\Controllers\User;

use App\Enums\ProviderEnum;
use Tests\AuthenticatedTestCase;

class SettingsControllerTest extends AuthenticatedTestCase
{
    public function test_update_settings(): void
    {
        $response = $this->post(
            '/api/settings',
            [
                'provider' => ProviderEnum::ANTHROPIC->value,
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'result',
        ]);
        $this->assertDatabaseHas('users',
            [
                'id' => $this->user->id,
                'provider' => ProviderEnum::ANTHROPIC->value,
            ]
        );
    }
}
