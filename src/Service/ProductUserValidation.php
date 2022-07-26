<?php

namespace App\Service;

use App\Service\Interfaces\ProductUserValidationInterface;

class ProductUserValidation implements ProductUserValidationInterface
{
    public function productEditValidate(string $productOwnerName, string $currentUser): bool
    {
        if ($productOwnerName == $currentUser) {
            return true;
        } else {
            return false;
        }
    }

    public function productOpinionValidate(string $productOwnerName, string $currentUser): bool
    {
        if ($productOwnerName != $currentUser) {
            return true;
        } else {
            return false;
        }
    }
}
