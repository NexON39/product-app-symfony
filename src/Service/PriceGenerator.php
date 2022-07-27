<?php

namespace App\Service;

use App\Service\Interfaces\PriceGeneratorInterface;

class PriceGenerator implements PriceGeneratorInterface
{
    public function getProductPrice($product)
    {
        if (strlen($product) % 2 == 0) {
            return 20;
        } else {
            return 10;
        }
    }
}
