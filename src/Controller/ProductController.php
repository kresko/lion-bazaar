<?php

namespace App\Controller;

use App\Entity\Product;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
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
        // nastavi
    }
}