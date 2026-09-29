<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Http\Resources\PlaylistResource;

class PlaylistController extends Controller
{
public function index()
{
    $playlists = Playlist::with([
        'games.ratings',
        'games.genres',
        'games.platforms',
        'games.images',
    ])->get();

    return PlaylistResource::collection($playlists);
}

public function show(Playlist $playlist)
{
    $playlist->load([
        'games.ratings',
        'games.genres',
        'games.platforms',
        'games.images',
    ]);

    return new PlaylistResource($playlist);
}
}