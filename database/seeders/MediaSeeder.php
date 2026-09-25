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


        /*
         |--------------------------------------------------------------------------
         | Media Folder
         |--------------------------------------------------------------------------
         |
         | storage/app/public/media/games
         |
         */

        $files = $disk->allFiles('media/games');


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

                    'directory' => dirname($path),

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
                "Media seeded: {$path} - ID: {$media->id}"
            );

        }
    }
}