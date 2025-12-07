<?php

namespace App\Service\Builder\Url;

use App\Entity\Product;

class ProductUrlBuilder implements ProductUrlBuilderInterface
{
    public function buildUrlFromProductKey(Product $product): string
    {
        return '/' . str_replace('_', '-', $product->getProductKey());
    }
}
