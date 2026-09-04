<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;

class PlaylistController extends Controller
{
    public function index()
    {
        return response()->json(
            Playlist::with('games')->get()
        );
    }

    public function show(Playlist $playlist)
    {
        $playlist->load('games');

        return response()->json($playlist);
    }
}