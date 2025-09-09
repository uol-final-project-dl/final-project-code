<?php

namespace App\Services\Github;

use App\Services\FileParsing\FileParsingService;
use GrahamCampbell\GitHub\Facades\GitHub;
use Illuminate\Http\Client\ConnectionException;

class GithubRepositoriesService
{
    /**
     * @throws ConnectionException
     */
    public static function getAllRepositories(): array
    {
        if (!config('github.connections.main.token')) {
            return [];
        }

        $repositories = GitHub::me()->repositories([
            'type' => 'all',
            'sort' => 'updated',
            'direction' => 'desc',
            'per_page' => 100,
        ]);

        // I only want title and id of the repository to make the select dropdown
        return collect($repositories)->map(function ($repository) {
            return [
                'id' => $repository['id'],
                'name' => $repository['name'],
            ];
        })->toArray();
    }

    public static function saveFilesFromRepo(int $projectId, string|int $repositoryId): void
    {
        [$owner, $name, $ref] = self::getRepoInfo($repositoryId);
        $path = '';

        self::crawlPath($projectId, $owner, $name, $path, $ref);

    }

    /**
     * @throws ConnectionException
     */
    private static function crawlPath(int $projectId, string $owner, string $repo, string $path = '', string $ref = 'main'): void
    {
        $contentsApi = GitHub::repo()->contents();

        $items = $contentsApi->show($owner, $repo, $path, $ref);

        $list = isset($items['type']) ? [$items] : $items;

        foreach ($list as $item) {
            if ($item['type'] === 'dir') {
                self::crawlPath($projectId, $owner, $repo, $item['path'], $ref);
                continue;
            }

            if ($item['type'] === 'file') {
                $content = $contentsApi->download($owner, $repo, $item['path'], $ref);
                $url = $item['download_url'];

                FileParsingService::parseFile($projectId, $url, $content);
            }
        }
    }

    public static function createBranch(string $repositoryId, string $branchName): void
    {
        [$owner, $name, $ref] = self::getRepoInfo($repositoryId);

        $gitApi = GitHub::gitData();

        $baseBranchInfo = $gitApi->references()->show($owner, $name, "heads/$ref");
        $baseSha = $baseBranchInfo['object']['sha'];

        $gitApi->references()->create($owner, $name,
            [
                "ref" => "refs/heads/$branchName",
                "sha" => $baseSha
            ]
        );
    }

    private static function getRepoInfo(string $repositoryId): array
    {
        $repo = Github::repo()->showById($repositoryId);

        if (empty($repo) || !isset($repo['owner']['login']) || !isset($repo['name'])) {
            return [
                '',
                '',
                ''
            ];
        }

        $owner = $repo['owner']['login'];
        $name = $repo['name'];
        $ref = $repo['default_branch'] ?? 'main';

        return [
            $owner,
            $name,
            $ref,
        ];
    }

    public static function updateFile(
        string $repositoryId,
        string $branchName,
        string $filePath,
        string $content,
        string $commitMessage
    ): void
    {
        [$owner, $name] = self::getRepoInfo($repositoryId);

        $gitApi = GitHub::repo();

        $fileInfo = $gitApi->contents()->show($owner, $name, $filePath, $branchName);
        $sha = $fileInfo['sha'] ?? null;

        $committer = array('name' => 'BrainstormingTool', 'email' => config('github.committer_email'));

        // Update the file
        $gitApi->contents()->update(
            $owner,
            $name,
            $filePath,
            $content,
            $commitMessage,
            $sha,
            $branchName,
            $committer
        );
    }

    public static function createFile(
        string $repositoryId,
        string $branchName,
        string $filePath,
        string $content,
        string $commitMessage
    ): void
    {
        [$owner, $name] = self::getRepoInfo($repositoryId);

        $committer = array('name' => 'BrainstormingTool', 'email' => config('github.committer_email'));

        $gitApi = GitHub::repo();

        $gitApi->contents()->create(
            $owner,
            $name,
            $filePath,
            $content,
            $commitMessage,
            $branchName,
            $committer
        );
    }

    public static function createPullRequest(
        string $repositoryId,
        string $branchName,
        string $title,
        string $body = ''
    ): void
    {
        [$owner, $name, $ref] = self::getRepoInfo($repositoryId);

        $gitApi = GitHub::pull_request();

        $gitApi->create(
            $owner,
            $name,
            [
                'title' => $title,
                'head' => $branchName,
                'base' => $ref,
                'body' => $body,
            ]
        );
    }

    public static function getRepositoryUrlToBranchDiff(string|int $repositoryId, string $branchName): string|null
    {
        [$owner, $name, $ref] = self::getRepoInfo($repositoryId);

        if (empty($owner) || empty($name) || empty($ref)) {
            return null;
        }

        return "https://github.com/$owner/$name/compare/$ref...$branchName";
    }
}
