<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class MainController extends Controller
{
    public function index(): View
    {
        return view('user');
    }
}

