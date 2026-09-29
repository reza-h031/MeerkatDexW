<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\GameRequirement;
class Game extends Model
{
    protected $fillable = [
        'title',
        'description',
        'release_date',
        'developer_id',
        'publisher_id',
        'website',
        'status',
    ];


    public function developer()
    {
        return $this->belongsTo(Developer::class);
    }


    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }


public function platforms()
{
    return $this->belongsToMany(Platform::class)
        ->withPivot([
            'version',
            'release_date',
            'download_size',
            'game_requirement_id',
        ]);
}


    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }


    public function images()
    {
        return $this->hasMany(GameImage::class);
    }


    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }


public function playlists()
{
    return $this->belongsToMany(
        Playlist::class,
        'playlist_game'
    );
}
}