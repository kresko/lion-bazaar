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

        $categoryRepository = $em->getRepository(Category::class);
        $created = [];
        $updated = [];

        foreach ($data[self::CATEGORIES] as $categoryData) {
            $category = $categoryRepository->findOneBy(['category_key' => $categoryData['category_key']]);

            if (!$category) {
                $category = new Category();
                $category->setCreatedAtValue(new \DateTimeImmutable());
                $created[] = $categoryData['category_key'];
            } else {
                $updated[] = $categoryData['category_key'];
            }

            $category
                ->setNodeOrder((int)($categoryData['node_order']))
                ->setCategoryKey($categoryData['category_key'])
                ->setParentCategoryKey($categoryData['parent_category_key'])
                ->setName($categoryData['name'])
                ->setIsRoot((bool)($categoryData['is_root']))
                ->setUpdatedAtValue(new \DateTime());

            $em->persist($category);
        }

        $em->flush();

        return $this->json([
            'status' => 'Category created',
            'created' => $created,
            'updated' => $updated,
            'errors' => $data[CategoryValidator::ERRORS] ?? []
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
