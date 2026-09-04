<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaVariants extends Model
{
    /** @use HasFactory<\Database\Factories\MediaVariantsFactory> */
    use HasFactory;

    protected $fillable = [
        "media_id",
        "variant",
        "path",
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
