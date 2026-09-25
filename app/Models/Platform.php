<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'logo',
    ];

    public function games()
    {
        return $this->belongsToMany(Game::class)
            ->withPivot([
                'version',
                'release_date',
                'download_size',
                'game_requirement_id',
            ]);
    }
}