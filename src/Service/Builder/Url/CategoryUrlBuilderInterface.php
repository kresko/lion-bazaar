<?php

namespace App\Service\Builder\Url;

use App\Entity\Category;

interface CategoryUrlBuilderInterface
{
    /**
     * @param Category $category
     *
     * @return string|null
     */
    public function buildUrlFromCategory(Category $category): ?string;
}
