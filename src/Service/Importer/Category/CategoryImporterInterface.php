<?php

namespace App\Service\Importer\Category;

use App\Entity\Category;

interface CategoryImporterInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, list<string>>
     */
    public function importCategories(array $data): array;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $records
     *
     * @return array<string, mixed>
     */
    public function importUrls(array $data, array $records): array;

    /**
     * @param Category $category
     *
     * @return void
     */
    public function removeCategory(Category $category): void;
}
