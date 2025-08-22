<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Github\GithubRepositoriesService;
use App\Services\WebSocket\PusherService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'githubRepositories' => GithubRepositoriesService::getAllRepositories(),
            'userId' => Auth::id(),
        ];
    }

    /**
     * @throws BindingResolutionException
     */
    public function pusherAuth(Request $request): void
    {
        $pusherService = PusherService::make();
        $user = User::safeInstance(Auth::user());

        $input = $request->all();

        echo $pusherService->socket_auth('private-user-channel-' . $user->id, $input['socket_id']);
    }
}

