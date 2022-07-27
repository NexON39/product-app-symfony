<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\ProductUserValidation;

class ProductUserValidationTest extends TestCase
{
    public function testProductEditValidate(): void
    {
        $validate = new ProductUserValidation;
        $result = $validate->productEditValidate('user1', 'user1');
        $this->assertSame(true, $result);
    }

    public function testWrongProductEditValidate(): void
    {
        $validate = new ProductUserValidation;
        $result = $validate->productEditValidate('user1', 'user2');
        $this->assertSame(false, $result);
    }

    public function testProductOpinionValidate(): void
    {
        $validate = new ProductUserValidation;
        $result = $validate->productOpinionValidate('user1', 'user1');
        $this->assertSame(false, $result);
    }

    public function testWrongProductOpinionValidate(): void
    {
        $validate = new ProductUserValidation;
        $result = $validate->productOpinionValidate('user1', 'user2');
        $this->assertSame(true, $result);
    }
}
