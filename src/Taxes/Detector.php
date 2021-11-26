<?php

namespace App\Taxes;

class Detector
{

    protected $price;

    public function __construct(float $price)
    {
        $this->price = $price;
    }

    public function detect(float $amount): bool
    {
        return $amount > $this->price ? true : false;
    }
}
