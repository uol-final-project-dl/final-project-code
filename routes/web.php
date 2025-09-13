<?php

use App\Http\Controllers\Projects\ProjectsController;
use App\Http\Controllers\Projects\SingleProjectController;
use App\Http\Controllers\Prototypes\FeedbackController;
use App\Http\Controllers\Prototypes\OpenBranchController;
use App\Http\Controllers\Prototypes\RemixPrototypeController;
use App\Http\Controllers\Prototypes\RetryFailedPrototypeController;
use App\Http\Controllers\Prototypes\ViewPrototypeController;
use App\Http\Controllers\User\LoginController;
use App\Http\Controllers\User\MainController;
use App\Http\Controllers\User\SettingsController;
use App\Http\Controllers\User\SignupController;
use Illuminate\Support\Facades\Route;

$authMiddleware = 'auth:sanctum';

Route::get('/', function () {
    return redirect()->route('users.home');
});

Route::get('/user/app/{path?}', [MainController::class, 'index'])
    ->where('path', '.*')
    ->name('users.home');

Route::get('/user/logout', [LoginController::class, 'logout']);

Route::post('/user/pusher/auth', [MainController::class, 'pusherAuth'])
    ->middleware($authMiddleware)
    ->name('pusher.auth');

Route::group(['prefix' => 'api', 'as' => 'api.'], static function () use ($authMiddleware) {
    Route::get('/getData', [MainController::class, 'getData'])
        ->middleware($authMiddleware)
        ->name('getData');

    Route::post('/user/postLogin', [LoginController::class, 'postLogin']);
    Route::post('/user/postSignup', [SignupController::class, 'postSignup']);

    Route::get('/checkUserStatus', static function () {
        return response()->json([
            'isAuthenticated' => true,
            'provider' => auth()->user()->provider ?? 'openai',
        ]);
    })->middleware($authMiddleware);

    Route::middleware([$authMiddleware])->group(function () {
        // SETTINGS
        Route::post('/settings', [SettingsController::class, 'updateSettings']);

        // PROJECTS LIST
        Route::get('/projects', [ProjectsController::class, 'getProjects']);
        Route::post('/project/create', [ProjectsController::class, 'createProject']);

        // SINGLE PROJECT
        Route::get('/project/{id}', [SingleProjectController::class, 'getProject']);
        Route::delete('/project/{id}', [SingleProjectController::class, 'deleteProject']);
        Route::post('/project/{id}/update-stage', [SingleProjectController::class, 'updateProjectStage']);
        Route::post('/project/{id}/update-status', [SingleProjectController::class, 'updateProjectStatus']);

        // BRAINSTORMING
        Route::post('/project/{id}/brainstorming/upload-documents', [SingleProjectController::class, 'uploadDocuments']);

        // PROTOTYPING
        Route::post('/project/{project}/prototype/{prototype}/remix', [RemixPrototypeController::class, 'remixPrototype']);
        Route::get('/project/{project}/prototype/{prototype}/retry', [RetryFailedPrototypeController::class, 'retryPrototype']);
        Route::post('/project/{project}/prototype/{prototype}/feedback', [FeedbackController::class, 'saveFeedback']);

    });
});

// PROTOTYPES
Route::middleware([$authMiddleware])->group(function () {
    Route::get('/prototype/{prototype}/asset/{file?}', [ViewPrototypeController::class, 'getPrototypeFile'])
        ->where('file', '.*');

    Route::get('/prototype/{prototype}', [ViewPrototypeController::class, 'viewPrototype']);
});

Route::middleware([$authMiddleware])->group(function () {
    Route::get('/branch/{prototype}', [OpenBranchController::class, 'redirectToBranch']);
});


