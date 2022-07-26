<?php

namespace App\Service\Interfaces;

interface PriceGeneratorInterface
{
    public function getProductPrice($product): int;
}
