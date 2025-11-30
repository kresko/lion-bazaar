<?php

namespace App\Tests\Unit\Service\Importer\Product;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\Url;
use App\Service\Builder\Url\ProductUrlBuilderInterface;
use App\Service\Importer\Product\ProductImporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class ProductImporterTest extends TestCase
{
    public function testImportProductsCreatesAndPersists(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $productRepo = $this->createMock(EntityRepository::class);
        $productRepo->method('findOneBy')->willReturn(null);

        $em->method('getRepository')->with(Product::class)->willReturn($productRepo);

        $persisted = [];
        $em->method('persist')->willReturnCallback(function ($e) use (&$persisted) {
            $persisted[] = $e;
        });

        $flushed = 0;
        $em->method('flush')->willReturnCallback(function () use (&$flushed) {
            $flushed++;
        });

        $builder = $this->createMock(ProductUrlBuilderInterface::class);

        $importer = new ProductImporter($em, $builder);

        $data = [ProductImporter::PRODUCTS => [[
            'sku' => 'sku-1',
            'category_key' => 'cat',
            'product_key' => 'p1',
            'name' => 'Product 1',
            'description' => 'Desc'
        ]]];

        $result = $importer->importProducts($data);

        $this->assertArrayHasKey('created', $result);
        $this->assertCount(1, $persisted);
        $this->assertEquals(1, $flushed);
    }

    public function testImportProductCategoryMappingPersistsMapping(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $product = new Product();
        $category = new Category();

        $productRepo = $this->createMock(EntityRepository::class);
        $categoryRepo = $this->createMock(EntityRepository::class);
        $productCategoryRepo = $this->createMock(EntityRepository::class);

        $productRepo->method('findOneBy')->willReturn($product);
        $categoryRepo->method('findOneBy')->willReturn($category);
        $productCategoryRepo->method('findOneBy')->willReturn(null);

        $em->method('getRepository')->willReturnCallback(function ($class) use ($productRepo, $categoryRepo, $productCategoryRepo) {
            return match ($class) {
                Product::class => $productRepo,
                Category::class => $categoryRepo,
                ProductCategory::class => $productCategoryRepo,
                default => null
            };
        });

        $persisted = [];
        $em->method('persist')->willReturnCallback(function ($e) use (&$persisted) {
            $persisted[] = $e;
        });

        $flushed = 0;
        $em->method('flush')->willReturnCallback(function () use (&$flushed) {
            $flushed++;
        });

        $builder = $this->createMock(ProductUrlBuilderInterface::class);
        $importer = new ProductImporter($em, $builder);

        $data = [ProductImporter::PRODUCTS => [[
            'sku' => 'sku-1',
            'category_key' => 'cat'
        ]]];

        $importer->importProductCategoryMapping($data);

        $this->assertCount(1, $persisted);
        $this->assertEquals(1, $flushed);
    }

    public function testRemoveProductRemovesRelatedEntities(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);

        $urlRepo = $this->createMock(EntityRepository::class);
        $productCategoryRepo = $this->createMock(EntityRepository::class);

        $url = new Url();
        $pc = new ProductCategory();

        $urlRepo->method('findOneBy')->willReturn($url);
        $productCategoryRepo->method('findBy')->willReturn([$pc]);

        $em->method('getRepository')->willReturnCallback(function ($class) use ($urlRepo, $productCategoryRepo) {
            return $class === Url::class ? $urlRepo : $productCategoryRepo;
        });

        $removed = [];
        $em->method('remove')->willReturnCallback(function ($e) use (&$removed) {
            $removed[] = $e;
        });

        $flushed = 0;
        $em->method('flush')->willReturnCallback(function () use (&$flushed) {
            $flushed++;
        });

        $builder = $this->createMock(ProductUrlBuilderInterface::class);
        $importer = new ProductImporter($em, $builder);

        $product = new Product();

        $importer->removeProduct($product);

        // Expect url, productCategory and product removed (3 removes)
        $this->assertCount(3, $removed);
        $this->assertEquals(1, $flushed);
    }
}
