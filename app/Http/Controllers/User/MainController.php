<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Github\GithubRepositoriesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;

class MainController extends Controller
{
    public function index(): View
    {
        return view('user');
    }

    /**
     * @throws ConnectionException
     */
    public function getData(): array
    {
        return [
            'result' => 1,
            'githubRepositories' => GithubRepositoriesService::getAllRepositories()
        ];
    }
}

