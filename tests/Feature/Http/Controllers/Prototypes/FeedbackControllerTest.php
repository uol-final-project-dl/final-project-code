<?php

namespace Tests\Feature\Http\Controllers\Prototypes;

use App\Enums\StatusEnum;
use Tests\AuthenticatedTestCase;

class FeedbackControllerTest extends AuthenticatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->prototype->update([
            'status' => StatusEnum::READY->value
        ]);
    }

    public function test_save_feedback(): void
    {
        $feedbackScore = random_int(1, 5);
        $response = $this->post(
            '/api/project/' . $this->project->id . '/prototype/' . $this->prototype->id . '/feedback',
            [
                'feedback_score' => $feedbackScore,
            ]
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('prototypes', [
            'id' => $this->prototype->id,
            'feedback_score' => $feedbackScore,
        ]);
    }
}
