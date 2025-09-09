<?php

namespace Tests\Feature\Services\Github;

use App\Services\Github\GithubRepositoriesService;
use Illuminate\Support\Facades\Config;
use Mockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\AuthenticatedTestCase;

class GithubRepositoriesServiceTest extends AuthenticatedTestCase
{
    #[RunInSeparateProcess]
    public function test_get_all_repositories(): void
    {
        Config::set('github.connections.main.token', 'test-token');
        $repositoriesArray = [
            [
                'id' => 1,
                'name' => 'repo1'
            ],
            [
                'id' => 2,
                'name' => 'repo2'
            ]
        ];

        $responseMock = Mockery::mock('alias:GrahamCampbell\GitHub\Facades\GitHub');
        $responseMock->shouldReceive('me')->andReturnSelf();
        $responseMock->shouldReceive('repositories')->andReturn($repositoriesArray);

        $response = GithubRepositoriesService::getAllRepositories();

        $this->assertEquals($repositoriesArray, $response);
    }

    #[RunInSeparateProcess]
    public function test_save_files_from_repo(): void
    {
        Config::set('github.connections.main.token', 'test-token');
        $repositoryInfo = [
            'owner' => [
                'login' => 'test-owner',
                'name' => 'repo1'
            ],
            'id' => 1,
            'name' => 'repo1',
            'default_branch' => 'main',
        ];

        $responseMock = Mockery::mock('alias:GrahamCampbell\GitHub\Facades\GitHub');
        $responseMock->shouldReceive('repo')->andReturnSelf();
        $responseMock->shouldReceive('showById')->andReturn($repositoryInfo);
        $responseMock->shouldReceive('contents')->andReturnSelf();
        $responseMock->shouldReceive('show')->andReturn([
            [
                'type' => 'file',
                'path' => 'file1.txt',
                'download_url' => 'https://raw.githubusercontent.com/test-owner/repo1/main/file1.txt'
            ]
        ]);
        $responseMock->shouldReceive('download')->andReturn('File content');

        $fileParsingMock = Mockery::mock('alias:App\Services\FileParsing\FileParsingService');
        $fileParsingMock->shouldReceive('parseFile')->andReturnTrue();

        GithubRepositoriesService::saveFilesFromRepo($this->project->id, 1);

        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    public function test_create_branch(): void
    {
        Config::set('github.connections.main.token', 'test-token');
        $repositoryInfo = [
            'owner' => [
                'login' => 'test-owner',
                'name' => 'repo1'
            ],
            'id' => 1,
            'name' => 'repo1',
            'default_branch' => 'main',
        ];

        $responseMock = Mockery::mock('alias:GrahamCampbell\GitHub\Facades\GitHub');
        $responseMock->shouldReceive('repo')->andReturnSelf();
        $responseMock->shouldReceive('showById')->andReturn($repositoryInfo);
        $responseMock->shouldReceive('gitData')->andReturnSelf();
        $responseMock->shouldReceive('references')->andReturnSelf();
        $responseMock->shouldReceive('show')->andReturn([
            'object' => [
                'sha' => 'base-sha'
            ]
        ]);
        $responseMock->shouldReceive('create')->andReturnTrue();

        GithubRepositoriesService::createBranch('repo1', 'new-branch');

        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    public function test_update_file(): void
    {
        Config::set('github.connections.main.token', 'test-token');
        $repositoryInfo = [
            'owner' => [
                'login' => 'test-owner',
                'name' => 'repo1'
            ],
            'id' => 1,
            'name' => 'repo1',
            'default_branch' => 'main',
        ];

        $responseMock = Mockery::mock('alias:GrahamCampbell\GitHub\Facades\GitHub');
        $responseMock->shouldReceive('repo')->andReturnSelf();
        $responseMock->shouldReceive('showById')->andReturn($repositoryInfo);
        $responseMock->shouldReceive('contents')->andReturnSelf();
        $responseMock->shouldReceive('show')->andReturn([
            'sha' => 'base-sha'
        ]);
        $responseMock->shouldReceive('update')->andReturnTrue();

        GithubRepositoriesService::updateFile('repo1', 'new-branch', 'file1.txt', 'New content', 'Update file');

        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    public function test_create_file(): void
    {
        Config::set('github.connections.main.token', 'test-token');
        $repositoryInfo = [
            'owner' => [
                'login' => 'test-owner',
                'name' => 'repo1'
            ],
            'id' => 1,
            'name' => 'repo1',
            'default_branch' => 'main',
        ];

        $responseMock = Mockery::mock('alias:GrahamCampbell\GitHub\Facades\GitHub');
        $responseMock->shouldReceive('repo')->andReturnSelf();
        $responseMock->shouldReceive('showById')->andReturn($repositoryInfo);
        $responseMock->shouldReceive('contents')->andReturnSelf();
        $responseMock->shouldReceive('create')->andReturnTrue();

        GithubRepositoriesService::createFile('repo1', 'new-branch', 'file1.txt', 'New content', 'Update file');

        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    public function test_create_pull_request(): void
    {
        Config::set('github.connections.main.token', 'test-token');
        $repositoryInfo = [
            'owner' => [
                'login' => 'test-owner',
                'name' => 'repo1'
            ],
            'id' => 1,
            'name' => 'repo1',
            'default_branch' => 'main',
        ];

        $responseMock = Mockery::mock('alias:GrahamCampbell\GitHub\Facades\GitHub');
        $responseMock->shouldReceive('repo')->andReturnSelf();
        $responseMock->shouldReceive('showById')->andReturn($repositoryInfo);
        $responseMock->shouldReceive('pull_request')->andReturnSelf();
        $responseMock->shouldReceive('create')->andReturnTrue();

        GithubRepositoriesService::createPullRequest('repo1', 'new-branch', 'main', 'New PR');

        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    public function test_get_repository_url_to_branch_diff(): void
    {
        Config::set('github.connections.main.token', 'test-token');
        $repositoryInfo = [
            'owner' => [
                'login' => 'test-owner',
                'name' => 'repo1'
            ],
            'id' => 1,
            'name' => 'repo1',
            'default_branch' => 'main',
        ];

        $responseMock = Mockery::mock('alias:GrahamCampbell\GitHub\Facades\GitHub');
        $responseMock->shouldReceive('repo')->andReturnSelf();
        $responseMock->shouldReceive('showById')->andReturn($repositoryInfo);

        GithubRepositoriesService::getRepositoryUrlToBranchDiff('repo1', 'new-branch');

        $this->assertTrue(true);
    }
}
