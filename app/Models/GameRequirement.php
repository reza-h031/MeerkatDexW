<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Requirement;
use App\Models\Platform;
class GameRequirement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'minimum_requirements',
        'recommended_requirements',
    ];


public function minimumRequirement()
{
    return $this->belongsTo(Requirement::class, 'minimum_requirement_id');
}

public function recommendedRequirement()
{
    return $this->belongsTo(Requirement::class, 'recommended_requirement_id');
}
}
