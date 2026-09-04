<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
	public $timestamps = false;
    protected $fillable=[
        'game_id',
        'source',
        'logo_source',
        'rating',
        'rating_count'
    ];


    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
