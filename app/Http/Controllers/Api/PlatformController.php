<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;

class PlatformController extends Controller
{
    public function index()
    {
        return response()->json(
            Platform::all()
        );
    }

    public function show(Platform $platform)
    {
        return response()->json($platform);
    }
}