<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with([
            'developer',
            'publisher',
            'platforms',
            'genres',
            'images',
            'ratings',
        ])->get();

        return GameResource::collection($games);
    }

    public function show(Game $game)
    {
        $game->load([
            'developer',
            'publisher',
            'platforms',
            'genres',
            'images',
            'ratings',
        ]);

        return new GameResource($game);
    }
}