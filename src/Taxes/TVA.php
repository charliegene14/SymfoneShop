<?php

namespace App\Taxes;

class TVA
{
    protected $percentage;

    public function __construct(float $percentage)
    {
        $this->percentage = $percentage;
    }

    public function calcul(float $price): float
    {
        return $price * ($this->percentage / 100);
    }

    public function total(float $price): float
    {
        return $this->calcul($price) + $price;
    }
}
