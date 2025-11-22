<?php

namespace App\Service\Importer\Category;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Category;
use App\Entity\Url;
use App\Service\Builder\Url\CategoryUrlBuilderInterface;

class CategoryImporter implements CategoryImporterInterface
{
    /**
     * @var string
     */
    public const CATEGORIES = 'categories';

    public function __construct(
        private EntityManagerInterface $em,
        private CategoryUrlBuilderInterface $categoryUrlBuilder
    ) {
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function importCategories(array $data): array
    {
        $categoryRepository = $this->em->getRepository(Category::class);
        $created = [];
        $updated = [];

        foreach ($data[self::CATEGORIES] as $categoryData) {
            $category = $categoryRepository->findOneBy(['category_key' => $categoryData['category_key']]);

            if (!$category) {
                $category = new Category();
                $category->setCreatedAtValue(new \DateTimeImmutable());
                $created[] = 'Category: ' . $categoryData['category_key'];
            } else {
                $updated[] = 'Category: ' . $categoryData['category_key'];
            }

            $category
                ->setNodeOrder((int)($categoryData['node_order']))
                ->setCategoryKey($categoryData['category_key'])
                ->setParentCategoryKey($categoryData['parent_category_key'])
                ->setName($categoryData['name'])
                ->setIsRoot((bool)($categoryData['is_root']))
                ->setUpdatedAtValue(new \DateTime());

            $this->em->persist($category);
        }

        $this->em->flush();

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * @param array $data
     * @param array $records
     *
     * @return array
     */
    public function importUrls(array $data, array $records): array
    {
        $urlRepository = $this->em->getRepository(Url::class);
        $categoryRepository = $this->em->getRepository(Category::class);

        foreach ($data[self::CATEGORIES] as $categoryData) {
            $category = $categoryRepository->findOneBy(['category_key' => $categoryData['category_key']]);

            if (!$category) {
                continue;
            }

            $url = $urlRepository->findOneBy(['category' => $category->getId()]);

            $categoryUrl = $this->categoryUrlBuilder->buildUrlFromCategory($category, $this->em);


            if (!$url) {
                $url = new Url();

                $url
                    ->setCategory($category)
                    ->setUrl('/c' . $categoryUrl)
                    ->setCreatedAtValue(new \DateTimeImmutable());

                $records['created'][] = 'Url: ' . $categoryData['category_key'];
            } else {
                $url
                    ->setCategory($category)
                    ->setUrl('/c' . $categoryUrl)
                    ->setUpdatedAtValue(new \DateTime());

                $records['updated'][] = 'Url: ' . $categoryData['category_key'];
            }

            $this->em->persist($url);
        }

        $this->em->flush();

        return $records;
    }

    /**
     * @param Category $category
     *
     * @return void
     */
    public function removeCategory(Category $category): void
    {
        $urlRepository = $this->em->getRepository(Url::class);
        $url = $urlRepository->findOneBy(['category' => $category->getId()]);

        $this->em->remove($url);
        $this->em->remove($category);
        $this->em->flush();
    }
}
