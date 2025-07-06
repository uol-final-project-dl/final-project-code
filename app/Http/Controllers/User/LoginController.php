<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function postLogin(): array
    {
        if (Auth::attempt(request(['email', 'password']))) {
            return [
                'result' => 1,
            ];
        }

        return [
            'result' => 0,
            'message' => 'Invalid credentials.',
        ];
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('users.home');
    }
}

