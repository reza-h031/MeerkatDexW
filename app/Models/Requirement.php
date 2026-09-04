<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ram',
        'system_version',
        'cpu',
        'gpu',
        'storage',
    ];
}
