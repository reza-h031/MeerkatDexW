<?php

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\DeveloperController;
use App\Http\Controllers\Api\PublisherController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
Route::middleware('throttle:api')->group(function () {
Route::get('/games', [GameController::class, 'index']);
Route::post('/games/filter', [GameController::class, 'filter']);
Route::get('/games/{game}', [GameController::class, 'show']);

Route::get('/developers', [DeveloperController::class, 'index']);
Route::get('/developers/{developer}', [DeveloperController::class, 'show']);

Route::get('/publishers', [PublisherController::class, 'index']);
Route::get('/publishers/{publisher}', [PublisherController::class, 'show']);

Route::get('/platforms', [PlatformController::class, 'index']);
Route::get('/platforms/{platform}', [PlatformController::class, 'show']);

Route::get('/genres', [GenreController::class, 'index']);
Route::get('/genres/{genre}', [GenreController::class, 'show']);

Route::get('/playlists', [PlaylistController::class, 'index']);
Route::get('/playlists/{playlist}', [PlaylistController::class, 'show']);

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:auth-register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth-login');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    });

});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    Route::get('/user', [UserController::class, 'show']);

    Route::patch('/user', [UserController::class, 'update']);
	
	    // User avatar
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);

    Route::delete('/user/avatar', [UserController::class, 'deleteAvatar']);

});
});