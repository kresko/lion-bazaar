<?php

namespace App\Service\Importer\Product;

use App\Entity\Product;

interface ProductImporterInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, list<string>>
     */
    public function importProducts(array $data): array;

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function importProductCategoryMapping(array $data): void;

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function importProductUrls(array $data): array;

    /**
     * @param Product $product
     *
     * @return void
     */
    public function removeProduct(Product $product): void;
}
