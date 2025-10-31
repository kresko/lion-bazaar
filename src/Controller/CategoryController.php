<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Url;
use App\Entity\Product;
use App\Validator\CategoryValidator;
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

        // implementiraj logiku koja ce vratiti proizvode koji pripadaju trenutnoj kategoriji
        // obavezno vanjsku klasu koristi
        $categoryRepository = $em->getRepository(Category::class);
        $productRepository = $em->getRepository(Product::class);

        // find category by route name (you use category name in the route)
        $category = $categoryRepository->findOneBy(['name' => $categoryName]);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $rootKey = $category->getCategoryKey();

        // gather descendant category keys (uses repository helper)
        $descendantKeys = $categoryRepository->findDescendantCategoryKeys($rootKey);

        // include the root category as well
        $keys = array_merge([$rootKey], $descendantKeys);

        // fetch products that belong to any of those category keys
        $products = $productRepository->createQueryBuilder('p')
            ->where('p.category_key IN (:keys)')
            ->setParameter('keys', $keys)
            ->getQuery()
            ->getResult();

        return $this->render('category/index.html.twig', [
            'title' => ucfirst(strtolower($category->getName())),
            'subtitle' => 'Products for "' . $category->getName() . '" and its subcategories',
            'products' => $products,
            'category' => $category,
        ]);
    }

    #[Route('/category', name: 'category_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CategoryValidator $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $data = $validator->validate($data);

        $records = $this->importCategories($em, $data);
        $records = $this->importUrls($em, $data, $records);

        return $this->json([
            'status' => 'Category created',
            'created' => $records['created'],
            'updated' => $records['updated'],
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

    /**
     * @param EntityManagerInterface $em
     * @param array $data
     * 
     * @return array
     */
    protected function importCategories(EntityManagerInterface $em, array $data): array
    {
        $categoryRepository = $em->getRepository(Category::class);
        $created = [];
        $updated = [];

        foreach ($data[self::CATEGORIES] as $categoryData) {
            $category = $categoryRepository->findOneBy(['category_key' => $categoryData['category_key']]);

            if (!$category) {
                $category = new Category();
                $category->setCreatedAtValue(new \DateTimeImmutable());
                $created[] = 'Category: ' . $categoryData['category_key'];
            } else {
                $updated[] = 'Category: ' . $categoryData['category_key'];
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

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * @param EntityManagerInterface $em
     * @param array $data
     * @param array $records
     * 
     * @return array
     */
    protected function importUrls(EntityManagerInterface $em, array $data, array $records): array
    {
        $urlRepository = $em->getRepository(Url::class);
        $categoryRepository = $em->getRepository(Category::class);

        foreach ($data[self::CATEGORIES] as $categoryData) {
            $category = $categoryRepository->findOneBy(['category_key' => $categoryData['category_key']]);

            if (!$category) {
                continue;
            }

            $url = $urlRepository->findOneBy(['category' => $category->getId()]);

            $categoryUrl = $this->buildUrlFromCategory($category, $em);
            

            if (!$url) {
                $url = new Url();

                $url
                    ->setCategory($category)
                    ->setUrl('/c' . $categoryUrl)
                    ->setCreatedAtValue(new \DateTimeImmutable());

                $records['created'][] = 'Url: ' . $categoryData['category_key'];
            } else {
                $url
                    ->setCategory($category)
                    ->setUrl('/c' . $categoryUrl)
                    ->setUpdatedAtValue(new \DateTime());

                $records['updated'][] = 'Url: ' . $categoryData['category_key'];
            }

            $em->persist($url);
        }

        $em->flush();

        return $records;
    }

    /**
     * @param Category $category
     * @param EntityManagerInterface $em
     * 
     * @return string|null
     */
    protected function buildUrlFromCategory(Category $category, EntityManagerInterface $em): ?string
    {
        $categoryRepository = $em->getRepository(Category::class);
        $parentCategory = $categoryRepository->findOneBy(['category_key' => $category->getParentCategoryKey()]);

        if ($parentCategory) {
            $parentUrl = $this->buildUrlFromCategory($parentCategory, $em);

            return rtrim($parentUrl, '/') . '/' . $category->getName();
        }

        return null;
    }
}
