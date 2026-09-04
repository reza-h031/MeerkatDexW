<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Developer;

class DeveloperController extends Controller
{
    public function index()
    {
        return response()->json(
            Developer::all()
        );
    }

    public function show(Developer $developer)
    {
        return response()->json($developer);
    }
}