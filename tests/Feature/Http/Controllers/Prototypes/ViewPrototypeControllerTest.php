<?php

namespace Tests\Feature\Http\Controllers\Prototypes;

use App\Enums\StatusEnum;
use Illuminate\Support\Facades\Storage;
use Tests\AuthenticatedTestCase;

class ViewPrototypeControllerTest extends AuthenticatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->prototype->update([
            'status' => StatusEnum::READY->value
        ]);
    }

    public function test_view_prototype(): void
    {
        $response = $this->get(
            '/prototype/' . $this->prototype->id
        );

        $response->assertStatus(200);
        $response->assertViewIs('prototypes.viewer');
    }

    public function test_get_prototype_file(): void
    {
        $distDirectory = "jobs/{$this->prototype->uuid}/dist";
        $filePath = "{$distDirectory}/index.html";
        $disk = Storage::disk('local');
        $disk->put($filePath, '<html><body>Test Prototype</body></html>');

        $response = $this->get(
            '/prototype/' . $this->prototype->id . '/asset/index.html'
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertHeader('Content-Security-Policy', "default-src 'self';script-src  'self';connect-src 'self';style-src   'self' 'unsafe-inline';img-src  'self' data:;frame-ancestors 'self'; form-action 'self';");
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'no-referrer');
    }
}
