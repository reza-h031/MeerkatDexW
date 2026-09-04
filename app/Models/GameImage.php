<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameImage extends Model
{
	public $timestamps = false;
    protected $fillable=[
        'game_id',
        'path',
        'type'
    ];


    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
