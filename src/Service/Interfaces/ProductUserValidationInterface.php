<?php

namespace App\Service\Interfaces;

interface ProductUserValidationInterface
{
    public function productEditValidate(string $productOwnerName, string $currentUser): bool;

    public function productOpinionValidate(string $productOwnerName, string $currentUser): bool;
}
