<?php

namespace App\Services;

use App\Models\MyMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

class UserAvatarService
{
    public function update(User $user, UploadedFile $file): MyMedia
    {
        $filename = Str::uuid() . '.jpg';

        $directory = "media/avatars/{$user->id}";

        $path = "{$directory}/{$filename}";

        $disk = Storage::disk('public');

        $image = Image::make($file->getRealPath())
            ->orientate()
            ->fit(512, 512)
            ->encode('jpg', 85);

        $contents = (string) $image;

        $disk->put($path, $contents);

        try {
            return DB::transaction(function () use (
                $user,
                $filename,
                $directory,
                $path,
                $contents
            ) {
                $media = MyMedia::forceCreate([
                    'disk' => 'public',
                    'directory' => $directory,
                    'visibility' => 'public',
                    'name' => pathinfo($filename, PATHINFO_FILENAME),
                    'path' => $path,
                    'width' => 512,
                    'height' => 512,
                    'size' => strlen($contents),
                    'type' => 'image/jpeg',
                    'ext' => 'jpg',
                ]);

                $user->avatar_media_id = $media->id;
                $user->save();

                return $media;
            });
        } catch (Throwable $exception) {
            $disk->delete($path);

            throw $exception;
        }
    }

public function delete(User $user): void
{
    $media = $user->avatar_media;

    // Nothing to delete.
    if (!$media) {
        $user->avatar_media_id = null;
        $user->save();

        return;
    }

    DB::transaction(function () use ($user, $media) {
        $disk = Storage::disk($media->disk);

        // Delete the physical avatar file.
        if (
            $disk->exists($media->path) &&
            !$disk->delete($media->path)
        ) {
            throw new \RuntimeException(
                'Unable to delete avatar file.'
            );
        }

        // Remove the avatar reference from the user.
        $user->avatar_media_id = null;
        $user->save();

        // Remove the media record from the database.
        $media->delete();
    });
}
}