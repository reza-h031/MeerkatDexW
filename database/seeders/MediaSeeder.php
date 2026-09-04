<?php

namespace Database\Seeders;

use App\Models\MyMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('public');

        $files = $disk->files('media/images');

        $this->command->info(
            'Files found: ' . count($files)
        );

        foreach ($files as $path) {

            $filename = basename($path);

            $name = pathinfo(
                $filename,
                PATHINFO_FILENAME
            );

            $ext = strtolower(
                pathinfo(
                    $filename,
                    PATHINFO_EXTENSION
                )
            );

            $mimeType = $disk->mimeType($path);
            $size = $disk->size($path);

            $width = null;
            $height = null;
            $exif = null;

            /*
             * همان کاری که Uploader هنگام آپلود واقعی
             * برای فایل‌های قابل Resize انجام می‌دهد.
             */
            if (str_starts_with($mimeType, 'image/')) {
                try {
                    $image = Image::make(
                        $disk->path($path)
                    );

                    $image->orientate();

                    $width = $image->getWidth();
                    $height = $image->getHeight();
                    $exif = $image->exif();

                } catch (\Throwable $e) {
                    $this->command->warn(
                        "Could not read image metadata: {$filename}"
                    );
                }
            }

            $media = MyMedia::updateOrCreate(
                [
                    'path' => $path,
                ],
                [
                    'disk' => 'public',
                    'directory' => 'media',
                    'visibility' => 'public',

                    'name' => $name,
                    'ext' => $ext,

                    'type' => $mimeType,
                    'size' => $size,

                    'width' => $width,
                    'height' => $height,
                    'exif' => $exif,
                ]
            );

            $this->command->info(
                "Media seeded: {$filename} - ID: {$media->id}"
            );
        }
    }
}