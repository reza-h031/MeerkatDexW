<?php

namespace App\Utils;

class TwoValueFloatRange
{
    private float $min;
    private float $max;

    public function __construct($min = null, $max = null)
    {
        $this->min = $min ?? PHP_INT_MIN;
        $this->max = $max ?? PHP_INT_MAX;
    }

    /**
     * @return float
     */
    public function getMin(): float
    {
        return $this->min;
    }

    /**
     * @param float $min
     */
    public function setMin(float $min): void
    {
        $this->min = $min;
    }

    /**
     * @return float
     */
    public function getMax(): float
    {
        return $this->max;
    }

    /**
     * @param float $max
     */
    public function setMax(float $max): void
    {
        $this->max = $max;
    }


}
