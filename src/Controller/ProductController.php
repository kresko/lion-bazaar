<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\ProductCategory;
use App\Entity\Url;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public const PRODUCTS = 'products';

    #[Route('/product', name: 'product_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        //data validator

        // Implement product creation logic here
        $records = $this->importProducts($em, $data);
        $this->importProductCategoryMapping($em, $data);
        $this->importProductUrls($em, $data);

        return $this->json([
            'status' => 'Product created',
            'created' => $records['created'],
            'updated' => $records['updated'],
        ]);
    }

    protected function importProducts(EntityManagerInterface $em, array $data): array
    {
        $productRepository = $em->getRepository(Product::class);
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

            $em->persist($product);
        }

        $em->flush();

        return [
            'created' => $created,
            'updated' => $updated
        ];
    }

    protected function importProductCategoryMapping(EntityManagerInterface $em, array $data)
    {
        $categoryRepository = $em->getRepository(Category::class);
        $productRepository = $em->getRepository(Product::class);
        $productCategoryRepository = $em->getRepository(ProductCategory::class);

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

            $em->persist($productCategory);
        }

        $em->flush();
    }

    protected function importProductUrls(EntityManagerInterface $em, array $data)
    {
        // Implementation for importing product URLs
        $urlRepository = $em->getRepository(Url::class);
        $productRepository = $em->getRepository(Product::class);

        foreach ($data[self::PRODUCTS] as $productData) {
            $product = $productRepository->findOneBy(['product_key' => $productData['product_key']]);

            if (!$product) {
                continue;
            }

            $url = $urlRepository->findOneBy(['product' => $product->getId()]);

            $productUrl = $this->buildUrlFromProductKey($product, $em);
            

            if (!$url) {
                $url = new Url();

                $url
                    ->setCreatedAtValue(new \DateTimeImmutable());

                $records['created'][] = 'Url: ' . $productData['product_key'];
            } else {
                $records['updated'][] = 'Url: ' . $productData['product_key'];
            }

            $url
                ->setCategory(null)
                ->setProduct($product)
                ->setUrl('/p' . $productUrl)
                ->setUpdatedAtValue(new \DateTime());

            $em->persist($url);
        }

        $em->flush();

        return $records;
    }

    protected function buildUrlFromProductKey(Product $product, EntityManagerInterface $em): string
    {
        return '/' . str_replace('_', '-', $product->getProductKey());
    }
}