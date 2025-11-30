<?php

namespace App\Tests\Unit\Controller;

use App\Controller\CategoryController;
use App\Entity\Category;
use App\Entity\Product;
use App\Service\Importer\Category\CategoryImporterInterface;
use App\Service\Validator\Category\CategoryValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategoryControllerTest extends TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
    }

    public function testIndexRendersProducts(): void
    {
        $request = new Request();
        $request->attributes->set('category_key', 'Electronics');

        $category = new Category();
        $category->setName('Electronics');
        $category->setCategoryKey('electronics');

        $categoryRepo = $this->createMock(\App\Repository\CategoryRepository::class);
        $categoryRepo->method('findOneBy')->willReturn($category);
        $categoryRepo->method('findDescendantCategoryKeys')->willReturn(['tablets']);

        $product = new Product();
        $product->setName('Tablet 1');

        $productRepo = $this->getMockBuilder(\App\Repository\ProductRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchProductByCategoryKeys'])
            ->getMock();

        $productRepo->method('fetchProductByCategoryKeys')->willReturn([$product]);

        $this->em->method('getRepository')->willReturnOnConsecutiveCalls($categoryRepo, $productRepo);

        $controller = $this->getMockBuilder(CategoryController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('category/index.html.twig', $this->callback(function ($vars) {
                return isset($vars['products']) && is_array($vars['products']) && isset($vars['category']);
            }))
            ->willReturn(new Response('rendered'));

        $response = $controller->index($request, $this->em);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('rendered', $response->getContent());
    }

    public function testCreateReturns400OnInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], 'invalid json');

        $validator = $this->createMock(CategoryValidator::class);
        $importer = $this->createMock(CategoryImporterInterface::class);

        $controller = $this->getMockBuilder(CategoryController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Invalid JSON'], 400)
            ->willReturn(new JsonResponse(['error' => 'Invalid JSON'], 400));

        $response = $controller->create($request, $this->em, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateCallsImporterAndReturnsJson(): void
    {
        $payload = ['categories' => []];
        $request = new Request([], [], [], [], [], [], json_encode($payload));

        $validator = $this->createMock(CategoryValidator::class);
        $validator->method('validate')->willReturn($payload);

        $importer = $this->createMock(CategoryImporterInterface::class);
        $importer->method('importCategories')->willReturn(['created' => ['a'], 'updated' => []]);
        $importer->method('importUrls')->willReturn(['created' => ['u'], 'updated' => []]);

        $controller = $this->getMockBuilder(CategoryController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($data) {
                return isset($data['status']) && $data['status'] === 'Category created';
            }))
            ->willReturn(new JsonResponse(['status' => 'Category created']));

        $response = $controller->create($request, $this->em, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testDeleteNotFoundReturns404(): void
    {
        $mockRepo = $this->createMock(EntityRepository::class);
        $mockRepo->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($mockRepo);

        $importer = $this->createMock(CategoryImporterInterface::class);

        $controller = $this->getMockBuilder(CategoryController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Category not found'], 404)
            ->willReturn(new JsonResponse(['error' => 'Category not found'], 404));

        $response = $controller->delete('missing_key', $this->em, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteCallsImporterRemove(): void
    {
        $category = new Category();
        $mockRepo = $this->createMock(EntityRepository::class);
        $mockRepo->method('findOneBy')->willReturn($category);
        $this->em->method('getRepository')->willReturn($mockRepo);

        $importer = $this->createMock(CategoryImporterInterface::class);
        $importer->expects($this->once())->method('removeCategory')->with($category);

        $controller = $this->getMockBuilder(CategoryController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->method('json')->willReturnCallback(function ($data) {
            return new JsonResponse($data);
        });

        $response = $controller->delete('key', $this->em, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $payload = json_decode($response->getContent(), true);
        $this->assertEquals('Category deleted', $payload['status']);
    }
}
