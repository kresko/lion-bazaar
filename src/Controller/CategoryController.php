<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Service\Importer\Category\CategoryImporterInterface;
use App\Service\Validator\Category\CategoryValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @var string
     */
    public const CATEGORIES = 'categories';

    #[Route('/c/{category_key}', name: 'category_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $categoryName = $request->attributes->get('category_key');

        $categoryRepository = $em->getRepository(Category::class);
        $productRepository = $em->getRepository(Product::class);

        $category = $categoryRepository->findOneBy(['name' => $categoryName]);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $rootKey = $category->getCategoryKey();
        $descendantKeys = $categoryRepository->findDescendantCategoryKeys($rootKey);
        $keys = array_merge([$rootKey], $descendantKeys);

        $products = $productRepository->fetchProductByCategoryKeys($keys);

        return $this->render('category/index.html.twig', [
            'title' => ucfirst(strtolower($category->getName())),
            'subtitle' => 'Products for "' . $category->getName() . '" and its subcategories',
            'products' => $products,
            'category' => $category,
        ]);
    }

    #[Route('/category', name: 'category_create', methods: ['POST'])]
    public function create(Request $request, CategoryValidator $validator, CategoryImporterInterface $categoryImporter): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $data = $validator->validate($data);

        $records = $categoryImporter->importCategories($data);
        $records = $categoryImporter->importUrls($data, $records);

        return $this->json([
            'status' => 'Category created',
            'created' => $records['created'],
            'updated' => $records['updated'],
            'errors' => $data[CategoryValidator::ERRORS] ?? []
        ]);
    }

    #[Route('/category/{category_key}', name: 'category_delete', methods: ['DELETE'])]
    public function delete(string $category_key, EntityManagerInterface $em, CategoryImporterInterface $categoryImporter): JsonResponse
    {
        $categoryRepository = $em->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['category_key' => $category_key]);

        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $categoryImporter->removeCategory($category);

        return $this->json([
            'status' => 'Category deleted',
            'category_key' => $category_key,
        ]);
    }
}
