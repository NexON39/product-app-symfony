<?php

namespace App\Service;

class ProductUserValidation
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
