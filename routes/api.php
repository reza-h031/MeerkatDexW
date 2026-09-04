<?php

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\DeveloperController;
use App\Http\Controllers\Api\PublisherController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\PlaylistController;

Route::get('/games', [GameController::class, 'index']);
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