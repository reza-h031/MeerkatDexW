
<?php

namespace App\Services;

use App\Models\MyMedia;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use RuntimeException;
use Throwable;

class UserAvatarService
{
    public function update(User $user, UploadedFile $file): MyMedia
    {
        // Remove the previous avatar first.
        $this->delete($user);

        $filename = Str::uuid() . '.jpg';

        $directory = "media/avatars/{$user->id}";
        $path = "{$directory}/{$filename}";

        $disk = Storage::disk('public');

        $image = Image::make($file->getRealPath())
            ->orientate()
            ->fit(512, 512)
            ->encode('jpg', 85);

        $contents = (string) $image;

        try {
            if (!$disk->put($path, $contents)) {
                throw new RuntimeException(
                    'Unable to store avatar file.'
                );
            }

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
                    'name' => pathinfo(
                        $filename,
                        PATHINFO_FILENAME
                    ),
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

        if (!$media) {
            if ($user->avatar_media_id !== null) {
                $user->avatar_media_id = null;
                $user->save();
            }

            return;
        }

        // Remove the database reference first.
        DB::transaction(function () use ($user, $media) {
            $user->avatar_media_id = null;
            $user->save();

            $media->delete();
        });

        // Delete the physical file after the database transaction.
        $disk = Storage::disk($media->disk);

        if (
            $disk->exists($media->path) &&
            !$disk->delete($media->path)
        ) {
            throw new RuntimeException(
                'Unable to delete avatar file.'
            );
        }
    }
}
