<?php

use App\Http\Controllers\Projects\ProjectsController;
use App\Http\Controllers\Projects\SingleProjectController;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\MainController;
use App\Http\Controllers\User\SignupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('users.home');
});

Route::get('/user/app/{path?}', [MainController::class, 'index'])
    ->where('path', '.*')
    ->name('users.home');

Route::get('/user/logout', [LoginController::class, 'logout']);

Route::group(['prefix' => 'api', 'as' => 'api.'], static function () {
    Route::post('/user/postLogin', [LoginController::class, 'postLogin']);
    Route::post('/user/postSignup', [SignupController::class, 'postSignup']);

    Route::get('/checkUserStatus', static function () {
        return response()->json([
            'isAuthenticated' => true
        ]);
    })->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum'])->group(function () {
        // PROJECTS LIST
        Route::get('/projects', [ProjectsController::class, 'getProjects']);
        Route::post('/project/create', [ProjectsController::class, 'createProject']);

        // SINGLE PROJECT
        Route::get('/project/{id}', [SingleProjectController::class, 'getProject']);
        Route::post('/project/{id}/update-stage', [SingleProjectController::class, 'updateProjectStage']);
        Route::post('/project/{id}/update-status', [SingleProjectController::class, 'updateProjectStatus']);

        Route::post('/project/{id}/brainstorming/upload-documents', [SingleProjectController::class, 'uploadDocuments']);
    });
});


