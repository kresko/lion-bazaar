<?php

namespace App\Service\Builder\Url;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;

class CategoryUrlBuilder implements CategoryUrlBuilderInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * @param Category $category
     *
     * @return string|null
     */
    public function buildUrlFromCategory(Category $category): ?string
    {
        $categoryRepository = $this->em->getRepository(Category::class);
        $parentCategory = $categoryRepository->findOneBy(['category_key' => $category->getParentCategoryKey()]);

        if ($parentCategory) {
            $parentUrl = $this->buildUrlFromCategory($parentCategory) ?? '';

            return rtrim($parentUrl, '/') . '/' . $category->getName();
        }

        return null;
    }
}
