<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameRequirement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'minimum_requirement_id',
        'recommended_requirement_id',
    ];

    public function minimumRequirement()
    {
        return $this->belongsTo(
            Requirement::class,
            'minimum_requirement_id'
        );
    }

    public function recommendedRequirement()
    {
        return $this->belongsTo(
            Requirement::class,
            'recommended_requirement_id'
        );
    }
}