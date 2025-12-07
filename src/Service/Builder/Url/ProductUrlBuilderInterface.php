<?php

namespace App\Service\Builder\Url;

use App\Entity\Product;

interface ProductUrlBuilderInterface
{
    public function buildUrlFromProductKey(Product $product): string;
}
