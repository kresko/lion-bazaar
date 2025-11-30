<?php

namespace App\Tests\Unit\Service\Importer\Category;

use App\Entity\Category;
use App\Entity\Url;
use App\Entity\CategoryTree;
use App\Service\Importer\Category\CategoryImporter;
use App\Service\Builder\Url\CategoryUrlBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CategoryImporterTest extends TestCase
{
    private EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $em;
    private CategoryUrlBuilderInterface|\PHPUnit\Framework\MockObject\MockObject $urlBuilder;
    private CategoryImporter $importer;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->urlBuilder = $this->createMock(CategoryUrlBuilderInterface::class);
        $this->importer = new CategoryImporter($this->em, $this->urlBuilder);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $r = new \ReflectionClass($entity);
        $p = $r->getProperty('id');
        $p->setAccessible(true);
        $p->setValue($entity, $id);
    }

    public function testImportCategoriesCreatesAndPersists(): void
    {
        $data = [CategoryImporter::CATEGORIES => [
            ['node_order' => 1, 'category_key' => 'cat_1', 'parent_category_key' => null, 'name' => 'Cat 1', 'is_root' => true]
        ]];

        $catRepo = $this->createMock(EntityRepository::class);
        $catRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($catRepo) {
            if ($class === Category::class) {
                return $catRepo;
            }
            return null;
        });

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCategories($data);

        $this->assertCount(1, $result['created']);
        $this->assertCount(0, $result['updated']);
    }

    public function testImportUrlsCreatesUrlWhenMissing(): void
    {
        $category = new Category();
        $this->setEntityId($category, 42);
        $category->setCategoryKey('cat_1')->setName('Cat 1')->setNodeOrder(1)->setIsRoot(true)->setParentCategoryKey(null);
        $category->setCreatedAtValue();
        $category->setUpdatedAtValue();

        $data = [CategoryImporter::CATEGORIES => [ ['category_key' => 'cat_1'] ]];

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturn(null);

        $catRepo = $this->createMock(EntityRepository::class);
        $catRepo->method('findOneBy')->willReturn($category);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($urlRepo, $catRepo) {
            if ($class === Url::class) {
                return $urlRepo;
            }
            if ($class === Category::class) {
                return $catRepo;
            }
            return null;
        });

        $this->urlBuilder->method('buildUrlFromCategory')->willReturn('/cat-1');

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($e) use (&$persisted) {
            $persisted[] = $e;
        });
        $this->em->expects($this->once())->method('flush');

        $records = ['created' => [], 'updated' => []];
        $result = $this->importer->importUrls($data, $records);

        $this->assertCount(1, $result['created']);
        $this->assertStringContainsString('Url: cat_1', $result['created'][0]);
        $this->assertCount(1, $persisted);
        $this->assertInstanceOf(Url::class, $persisted[0]);
    }

    public function testRemoveCategoryRemovesUrlAndCategory(): void
    {
        $category = new Category();
        $this->setEntityId($category, 7);

        $url = new Url();
        $url->setUrl('/c/test');

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturn($url);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($urlRepo) {
            if ($class === Url::class) {
                return $urlRepo;
            }
            return null;
        });

        $removed = [];
        $this->em->expects($this->exactly(2))->method('remove')->willReturnCallback(function ($e) use (&$removed) {
            $removed[] = $e;
        });
        $this->em->expects($this->once())->method('flush');

        $this->importer->removeCategory($category);

        $this->assertCount(2, $removed);
        $this->assertInstanceOf(Url::class, $removed[0]);
        $this->assertInstanceOf(Category::class, $removed[1]);
    }
}
