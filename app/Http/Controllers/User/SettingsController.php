<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function updateSettings(): array
    {
        $user = User::safeInstance(Auth::user());
        $user->update(['provider' => request('provider')]);

        return [
            'result' => 1
        ];
    }
}

