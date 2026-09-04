<?php

namespace App\Observers;


use App\Models\MediaVariants;
use App\Models\MyMedia;
use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use function PHPUnit\Framework\throwException;

class MediaObserver
{
    const THUMB_DEFAULT_WIDTH = 200;
    const THUMB_DEFAULT_HEIGHT = 200;

    public function creating(Media $media): void
    {
//        dump($media);
//        $this->created($media);
    }

    /**
     * Handle the Media "created" event.
     */
    public function created(Media $media): void
    {
        throwException(new \Exception());


        // Only handle images
        if (!str_starts_with($media->type, 'image')) {
            return;
        }

        $disk = $media->disk; // usually "public"
        $originalPath = $media->path;

        $image = Image::make(Storage::disk($disk)->path($originalPath));

        // Create a thumbnail (200x200)
        $thumbPath = 'media/images/thumbs/' . $media->name . "." . $media->ext;
        Storage::disk($disk)->put($thumbPath, (string)$image->fit(200, 200)->encode($media->ext, 80));

        /*        // Create a medium size (800px wide)
                $mediumPath = 'media/medium/' . $media->uuid . '.jpg';
                Storage::disk($disk)->put($mediumPath, (string)$image->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode('jpg', 85));*/

        MediaVariants::query()->create([
            "media_id" => $media->id,
            "variant" => "thumb",
            "path" => $thumbPath,
        ]);
    }

    /**
     * Handle the Media "updated" event.
     */
    public function updated(MyMedia $media): void
    {
//        dump($media);
        if (!str_starts_with($media->type, "image")) {
            return;
        }

        $original = $media->getOriginal();
        $originalThumbPath = "media/images/thumbs/" . $original["name"] . "." . $original["ext"];
        $thumbPath = "media/images/thumbs/$media->name.$media->ext";
        $image = Image::make(Storage::disk($media->disk)->path($media->path));
        Storage::disk($original["disk"])->delete($originalThumbPath);
        Storage::disk($media->disk)->put($thumbPath, (string)$image->fit(
            self::THUMB_DEFAULT_WIDTH, self::THUMB_DEFAULT_HEIGHT
        )->encode($media->ext, 80));
        MediaVariants::query()->where("media_id", $media->id)
            ->where("variant", "thumb")
            ->update(["path" => $thumbPath]);
    }

    /**
     * Handle the Media "deleted" event.
     */
    public function deleted(Media $media): void
    {
        $thumbPath = "media/images/thumbs/$media->name.$media->ext";
        Storage::disk($media->disk)->delete($thumbPath);
    }

    /**
     * Handle the Media "restored" event.
     */
    public function restored(Media $media): void
    {
        echo "restored";
    }

    /**
     * Handle the Media "force deleted" event.
     */
    public function forceDeleted(Media $media): void
    {
        echo "forceDeleted";
    }
}
