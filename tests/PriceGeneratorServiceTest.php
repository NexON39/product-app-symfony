<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\PriceGenerator;

class PriceGeneratorServiceTest extends TestCase
{
    // testIsPriceGeneratorReturn10
    public function testIsPriceGeneratorReturn10(): void
    {
        $priceGenerator = new PriceGenerator;
        $result = $priceGenerator->getProductPrice('car');
        $this->assertSame(10, $result);
    }

    // testIsPriceGeneratorReturn20
    public function testIsPriceGeneratorReturn20(): void
    {
        $priceGenerator = new PriceGenerator;
        $result = $priceGenerator->getProductPrice('telephon');
        $this->assertSame(20, $result);
    }
}
