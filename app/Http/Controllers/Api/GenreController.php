<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        return response()->json(
            Genre::all()
        );
    }

    public function show(Genre $genre)
    {
        return response()->json($genre);
    }
}