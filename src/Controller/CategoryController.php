<?php

namespace App\Controller;

use App\Entity\Category;
use App\Validator\CategoryValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @var string
     */
    public const CATEGORIES = 'categories';

    #[Route('/category', name: 'category_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CategoryValidator $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $data = $validator->validate($data);

        // $category = new Category();
        // $category
        //     ->setNodeOrder((int)($data['node_order'] ?? 0))
        //     ->setCategoryKey($data['category_key'] ?? '')
        //     ->setParentCategoryKey($data['parent_category_key'] ?? null)
        //     ->setName($data['name'] ?? 'Unnamed')
        //     ->setIsRoot((bool)($data['is_root'] ?? false));

        // $em->persist($category);
        // $em->flush();

        return $this->json([
            'status' => 'Category created',
            'errors' => $data[CategoryValidator::ERRORS] ?? []
        ]);
    }
}
