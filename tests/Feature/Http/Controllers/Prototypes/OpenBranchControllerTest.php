<?php

namespace Tests\Feature\Http\Controllers\Prototypes;

use App\Enums\PrototypeTypeEnum;
use Mockery;
use Tests\AuthenticatedTestCase;

class OpenBranchControllerTest extends AuthenticatedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update([
            'github_repository_id' => fake()->uuid,
        ]);

        $this->prototype->update([
            'type' => PrototypeTypeEnum::PULL_REQUEST->value,
        ]);
    }

    public function test_redirect_to_branch(): void
    {
        $mock = Mockery::mock('alias:App\Services\Github\GithubRepositoriesService');
        $mock->shouldReceive('getRepositoryUrlToBranchDiff')->once()->andReturn(fake()->url());

        $response = $this->get('/branch/' . $this->prototype->id);
        $response->assertStatus(302);
    }
}
