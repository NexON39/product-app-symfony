<?php

namespace App\Service;

class PriceGenerator
{
    public function getProductPrice($product): int
    {
        if (strlen($product) % 2 == 0) {
            return 20;
        } else {
            return 10;
        }
    }
}
