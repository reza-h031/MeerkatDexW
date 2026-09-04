<?php

namespace Database\Factories;

class Utils
{
    static function getRandomRate(): float
    {
        return floor(fake()->randomFloat(min: 0,max: 5)*2)/2;
    }
}
