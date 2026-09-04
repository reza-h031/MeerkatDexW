<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MyMedia extends Media
{
    protected $table = "media";

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariants::class,"media_id","id");
    }

    public function getThumbUrlAttribute()
    {
        return $this->variants()->where("variant", "thumb")->value("path");
    }

//    public function getMediumUrlAttribute(): ?string
//    {
//        return $this->variants()->where("variant", "medium")->value("path");
//    }
}
