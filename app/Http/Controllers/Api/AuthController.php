<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken(
            $validated['device_name'] ?? 'Meerkat Dex Android',
            ['*'],
            now()->addDays(30)
        );

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token->plainTextToken,
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (
            !$user ||
            !Hash::check($validated['password'], $user->password)
        ) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken(
            $validated['device_name'] ?? 'Meerkat Dex Android',
            ['*'],
            now()->addDays(30)
        );

        return response()->json([
            'message' => 'Login successful',
            'token' => $token->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function logoutAll(): JsonResponse
    {
        request()->user()->tokens()->delete();

        return response()->json([
            'message' => 'All sessions have been logged out',
        ]);
    }
}