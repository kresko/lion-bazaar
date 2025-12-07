<?php

namespace App\Service\Importer\Product;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\ProductCategory;
use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use App\Service\Builder\Url\ProductUrlBuilderInterface;

class ProductImporter implements ProductImporterInterface
{
    public const PRODUCTS = 'products';

    public function __construct(
        private EntityManagerInterface $em,
        private ProductUrlBuilderInterface $productUrlBuilder
    ) {
    }

    public function importProducts(array $data): array
    {
        $productRepository = $this->em->getRepository(Product::class);
        $created = [];
        $updated = [];

        foreach ($data[self::PRODUCTS] as $productData) {
            $product = $productRepository->findOneBy(['sku' => $productData['sku']]);

            if (!$product) {
                $product = new Product();
                $product->setCreatedAt(new DateTimeImmutable());
                $created[] = 'Product: ' . $productData['sku'];
            } else {
                $updated[] = 'Product: ' . $productData['sku'];
            }

            $product->setCategoryKey($productData['category_key']);
            $product->setProductKey($productData['product_key']);
            $product->setSku($productData['sku']);
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setUpdatedAt(new \DateTime());

            $this->em->persist($product);
        }

        $this->em->flush();

        return [
            'created' => $created,
            'updated' => $updated
        ];
    }


    public function importProductCategoryMapping(array $data): void
    {
        $categoryRepository = $this->em->getRepository(Category::class);
        $productRepository = $this->em->getRepository(Product::class);
        $productCategoryRepository = $this->em->getRepository(ProductCategory::class);

        foreach ($data[self::PRODUCTS] as $productData) {
            $product = $productRepository->findOneBy(['sku' => $productData['sku']]);
            $category = $categoryRepository->findOneBy(['category_key' => $productData['category_key']]);

            if (!$product || !$category) {
                continue;
            }

            $productCategory = $productCategoryRepository->findOneBy([
                'fk_product' => $product,
                'fk_category' => $category
            ]);

            if (!$productCategory) {
                $productCategory = new ProductCategory();
                $productCategory->setCreatedAt(new DateTimeImmutable());
            }

            $productCategory->setFkProduct($product);
            $productCategory->setFkCategory($category);
            $productCategory->setUpdatedAt(new \DateTime());

            $this->em->persist($productCategory);
        }

        $this->em->flush();
    }

    public function importProductUrls(array $data): array
    {
        $urlRepository = $this->em->getRepository(Url::class);
        $productRepository = $this->em->getRepository(Product::class);
        $records = [];

        foreach ($data[self::PRODUCTS] as $productData) {
            $product = $productRepository->findOneBy(['product_key' => $productData['product_key']]);

            if (!$product) {
                continue;
            }

            $url = $urlRepository->findOneBy(['product' => $product->getId()]);

            $productUrl = $this->productUrlBuilder->buildUrlFromProductKey($product);


            if (!$url) {
                $url = new Url();

                $url
                    ->setCreatedAtValue();

                $records['created'][] = 'Url: ' . $productData['product_key'];
            } else {
                $records['updated'][] = 'Url: ' . $productData['product_key'];
            }

            $url
                ->setCategory(null)
                ->setProduct($product)
                ->setUrl('/p' . $productUrl)
                ->setUpdatedAtValue();

            $this->em->persist($url);
        }

        $this->em->flush();

        return $records;
    }

    /**
     * @param Product $product
     *
     * @return void
     */
    public function removeProduct(Product $product): void
    {
        $urlRepository = $this->em->getRepository(Url::class);
        $productCategoryRepository = $this->em->getRepository(ProductCategory::class);

        $url = $urlRepository->findOneBy(['product' => $product->getId()]);
        $productCategories = $productCategoryRepository->findBy(['fk_product' => $product->getId()]);

        $this->em->remove($url);

        foreach ($productCategories as $productCategory) {
            $this->em->remove($productCategory);
        }

        $this->em->remove($product);
        $this->em->flush();
    }
}
