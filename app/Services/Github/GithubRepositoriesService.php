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
        $repo = Github::repo()->showById($repositoryId);

        if (empty($repo) || !isset($repo['owner']['login']) || !isset($repo['name'])) {
            return;
        }

        $owner = $repo['owner']['login'];
        $name = $repo['name'];
        $ref = $repo['default_branch'] ?? 'main';
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
}
