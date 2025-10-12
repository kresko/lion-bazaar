<?php

namespace App\Controller\Cms;

use App\Entity\Category;
use App\Entity\Url;
use App\Validator\CategoryValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CmsSlotController extends AbstractController
{
    /**
     * @var string
     */
    public const CATEGORIES = 'categories';

    #[Route('/cms/slot', name: 'cms_slot_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        // promeni logiku da bude za cms block
        // $records = $this->importCategories($em, $data);
        // $records = $this->importUrls($em, $data, $records);

        return $this->json([
            // 'status' => 'Category created',
            // 'created' => $records['created'],
            // 'updated' => $records['updated'],
            // 'errors' => $data[CategoryValidator::ERRORS] ?? []
        ]);
    }

    #[Route('/category/{category_key}', name: 'category_delete', methods: ['DELETE'])]
    public function delete(string $category_key, EntityManagerInterface $em): JsonResponse
    {
        $categoryRepository = $em->getRepository(Category::class);
        $category = $categoryRepository->findOneBy(['category_key' => $category_key]);

        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        $em->remove($category);
        $em->flush();

        return $this->json([
            'status' => 'Category deleted',
            'category_key' => $category_key,
        ]);
    }
}
