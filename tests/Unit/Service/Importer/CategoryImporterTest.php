<?php

namespace App\Tests\Unit\Service\Importer;

use App\Entity\Category;
use App\Entity\Url;
use App\Service\Importer\Category\CategoryImporter;
use App\Service\Builder\Url\CategoryUrlBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CategoryImporterTest extends TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    private CategoryImporter $importer;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $builder = $this->createMock(CategoryUrlBuilderInterface::class);

        $this->importer = new CategoryImporter($this->em, $builder);
    }

    public function testImportCategoriesCreatesNewRecords(): void
    {
        $data = [
            'categories' => [
                [
                    'node_order' => 1,
                    'category_key' => 'electronics',
                    'parent_category_key' => null,
                    'is_root' => true,
                    'name' => 'Electronics',
                ],
                [
                    'node_order' => 2,
                    'category_key' => 'tablets',
                    'parent_category_key' => 'electronics',
                    'is_root' => false,
                    'name' => 'Tablets',
                ]
            ]
        ];

        $mockRepository = $this->createMock(EntityRepository::class);
        // No existing categories -> returns null for every lookup
        $mockRepository->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($mockRepository);

        $this->em->expects($this->exactly(2))->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCategories($data);

        $this->assertCount(2, $result['created']);
        $this->assertEmpty($result['updated']);
    }

    public function testImportUrlsCreatesUrlRecords(): void
    {
        $data = [
            'categories' => [
                [
                    'node_order' => 1,
                    'category_key' => 'electronics',
                    'parent_category_key' => null,
                    'is_root' => true,
                    'name' => 'Electronics',
                ],
                [
                    'node_order' => 2,
                    'category_key' => 'tablets',
                    'parent_category_key' => 'electronics',
                    'is_root' => false,
                    'name' => 'Tablets',
                ]
            ]
        ];

        $categoryRepo = $this->createMock(EntityRepository::class);
        $urlRepo = $this->createMock(EntityRepository::class);

        // When looking up category by key, return a Category object
        $categoryRepo->method('findOneBy')->willReturnCallback(function ($criteria) {
            $cat = new Category();
            $cat->setCategoryKey($criteria['category_key']);
            $cat->setName(ucfirst($criteria['category_key']));
            $cat->setNodeOrder(1);
            $cat->setIsRoot($criteria['category_key'] === 'electronics');
            $cat->setCreatedAtValue(new \DateTimeImmutable());
            $cat->setUpdatedAtValue(new \DateTime());
            return $cat;
        });

        // URL lookup returns null (no existing url)
        $urlRepo->method('findOneBy')->willReturn(null);

        // em->getRepository should return urlRepo for Url::class and categoryRepo for Category::class
        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($urlRepo, $categoryRepo) {
            if ($class === Url::class) {
                return $urlRepo;
            }
            return $categoryRepo;
        });

        // builder should be invoked; we replace the real importer with one that has a builder mock
        $builder = $this->createMock(CategoryUrlBuilderInterface::class);
        $builder->method('buildUrlFromCategory')->willReturnOnConsecutiveCalls('/Electronics', '/Electronics/Tablets');

        // create importer with builder mock
        $importer = new CategoryImporter($this->em, $builder);

        $this->em->expects($this->exactly(2))->method('persist'); // two urls
        $this->em->expects($this->once())->method('flush');

        $records = ['created' => [], 'updated' => []];
        $result = $importer->importUrls($data, $records);

        $this->assertNotEmpty($result['created']);
    }

    public function testRemoveCategoryCallsRemoveAndFlush(): void
    {
        $category = new Category();

        $this->em->expects($this->once())->method('remove')->with($category);
        $this->em->expects($this->once())->method('flush');

        $this->importer->removeCategory($category);
    }
}
