<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = [
        'name',
        'description',
        'number',
    ];

    public function games()
    {
        return $this->belongsToMany(Game::class);
    }
}