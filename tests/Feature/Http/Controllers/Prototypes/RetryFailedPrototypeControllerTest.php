<?php

namespace Tests\Feature\Http\Controllers\Prototypes;

use App\Enums\StatusEnum;
use App\Jobs\Prototypes\GeneratePrototype;
use Illuminate\Support\Facades\Queue;
use Tests\AuthenticatedTestCase;

class RetryFailedPrototypeControllerTest extends AuthenticatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->prototype->update([
            'status' => StatusEnum::FAILED->value
        ]);
    }

    public function test_retry_prototype(): void
    {
        Queue::fake();

        $response = $this->get(
            '/api/project/' . $this->project->id . '/prototype/' . $this->prototype->id . '/retry'
        );

        Queue::assertPushed(GeneratePrototype::class);

        $response->assertStatus(200);
        $this->assertDatabaseHas('prototypes', [
            'id' => $this->prototype->id,
            'status' => StatusEnum::QUEUED->value,
        ]);
    }
}
