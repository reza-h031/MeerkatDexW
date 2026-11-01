<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\AvatarUploadRequest;
use App\Services\UserAvatarService;
class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(
        UpdateProfileRequest $request
    ): JsonResponse {
        $user = $request->user();

        $validated = $request->validated();

        if (
            isset($validated['email']) &&
            $validated['email'] !== $user->email
        ) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user->refresh()),
        ]);
    }
	public function updateAvatar(
    AvatarUploadRequest $request,
    UserAvatarService $avatarService
): JsonResponse {
    $media = $avatarService->update(
        $request->user(),
        $request->file('avatar')
    );

    return response()->json([
        'message' => 'Avatar updated successfully',
        'avatar' => \Illuminate\Support\Facades\Storage::disk(
            $media->disk
        )->url($media->path),
    ]);
}

public function deleteAvatar(
    Request $request,
    UserAvatarService $avatarService
): JsonResponse {
    $avatarService->delete($request->user());

    return response()->json([
        'message' => 'Avatar deleted successfully',
    ]);
}
	
}