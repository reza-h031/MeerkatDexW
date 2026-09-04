<?php

namespace App\Utils;

class TwoValueIntegerRange
{
    private int $min;
    private int $max;

    public function __construct($min = null, $max = null)
    {
        $this->min = $min ?? PHP_INT_MIN;
        $this->max = $max ?? PHP_INT_MAX;
    }

    /**
     * @return int
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * @param int $min
     */
    public function setMin(int $min): void
    {
        $this->min = $min;
    }

    /**
     * @return int
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * @param int $max
     */
    public function setMax(int $max): void
    {
        $this->max = $max;
    }
}
