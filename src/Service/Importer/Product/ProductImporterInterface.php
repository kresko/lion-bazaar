<?php

namespace App\Service\Importer\Product;

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
     * @param array<string, mixed> $records
     *
     * @return array<string, mixed>
     */
    public function importProductCategoryMapping(array $data): void;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $records
     *
     * @return array<string, mixed>
     */
    public function importProductUrls(array $data): array;
}