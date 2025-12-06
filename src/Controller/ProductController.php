<?php

namespace App\Controller;

use App\Service\Importer\Product\ProductImporterInterface;
use App\Service\Validator\Product\ProductValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;

class ProductController extends AbstractController
{
    public const PRODUCTS = 'products';

    #[Route('/product', name: 'product_create', methods: ['POST'])]
    public function create(Request $request, ProductValidator $productValidator, ProductImporterInterface $productImporter): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $productValidator->validate($data);

        $records = $productImporter->importProducts($data);
        $productImporter->importProductCategoryMapping($data);
        $productImporter->importProductUrls($data);

        return $this->json([
            'status' => 'Product created',
            'created' => $records['created'],
            'updated' => $records['updated'],
        ]);
    }

    #[Route('/product/{product_key}', name: 'product_delete', methods: ['DELETE'])]
    public function delete(string $product_key, EntityManagerInterface $em, ProductImporterInterface $productImporter): JsonResponse
    {
        $productRepository = $em->getRepository(Product::class);
        $product = $productRepository->findOneBy(['product_key' => $product_key]);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $productImporter->removeProduct($product);

        return $this->json([
            'status' => 'Product deleted',
            'product_key' => $product_key,
        ], 200);
    }
}
