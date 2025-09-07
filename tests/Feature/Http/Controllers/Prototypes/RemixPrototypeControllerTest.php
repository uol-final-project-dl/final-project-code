<?php

namespace Tests\Feature\Http\Controllers\Prototypes;

use App\Enums\StatusEnum;
use App\Jobs\Prototypes\GeneratePrototype;
use Illuminate\Support\Facades\Queue;
use Tests\AuthenticatedTestCase;

class RemixPrototypeControllerTest extends AuthenticatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->prototype->update([
            'status' => StatusEnum::READY->value
        ]);
    }

    public function test_remix_prototype(): void
    {
        Queue::fake();

        $response = $this->post(
            '/api/project/' . $this->project->id . '/prototype/' . $this->prototype->id . '/remix',
            [
                'description' => 'This is a remix description',
            ]
        );

        Queue::assertPushed(GeneratePrototype::class);

        $response->assertStatus(200);
        $this->assertDatabaseHas('prototypes', [
            'id' => $this->prototype->id,
            'status' => StatusEnum::QUEUED->value,
        ]);
    }
}
