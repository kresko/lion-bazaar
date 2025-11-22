<?php

namespace App\Service\Importer\Category;

use App\Entity\Category;

interface CategoryImporterInterface
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function importCategories(array $data): array;

    /**
     * @param array $data
     * @param array $records
     *
     * @return array
     */
    public function importUrls(array $data, array $records): array;

    /**
     * @param Category $category
     *
     * @return void
     */
    public function removeCategory(Category $category): void;
}
