<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Publisher;

class PublisherController extends Controller
{
    public function index()
    {
        return response()->json(
            Publisher::all()
        );
    }

    public function show(Publisher $publisher)
    {
        return response()->json($publisher);
    }
}