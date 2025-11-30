<?php

namespace App\Tests\Unit\Controller;

use App\Controller\ProductController;
use App\Entity\Product;
use App\Service\Importer\Product\ProductImporterInterface;
use App\Service\Validator\Product\ProductValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductControllerTest extends TestCase
{
    private $em;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
    }

    public function testCreateReturns400OnInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], 'not json');

        $validator = $this->createMock(ProductValidator::class);
        $importer = $this->createMock(ProductImporterInterface::class);

        $controller = $this->getMockBuilder(ProductController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Invalid JSON'], 400)
            ->willReturn(new JsonResponse(['error' => 'Invalid JSON'], 400));

        $response = $controller->create($request, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateCallsImporterAndReturnsJson(): void
    {
        $payload = ['products' => [[
            'sku' => 'sku-1',
            'category_key' => 'cat',
            'product_key' => 'p1',
            'name' => 'Product 1',
            'description' => 'Desc'
        ]]];

        $request = new Request([], [], [], [], [], [], json_encode($payload));

        $validator = $this->createMock(ProductValidator::class);
        $validator->expects($this->once())->method('validate')->with($payload);

        $importer = $this->createMock(ProductImporterInterface::class);
        $importer->method('importProducts')->willReturn(['created' => ['p'], 'updated' => []]);
        $importer->expects($this->once())->method('importProductCategoryMapping')->with($payload);
        $importer->expects($this->once())->method('importProductUrls')->with($payload);

        $controller = $this->getMockBuilder(ProductController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($data) {
                return isset($data['status']) && $data['status'] === 'Product created';
            }))
            ->willReturn(new JsonResponse(['status' => 'Product created']));

        $response = $controller->create($request, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testDeleteNotFoundReturns404(): void
    {
        $mockRepo = $this->createMock(EntityRepository::class);
        $mockRepo->method('findOneBy')->willReturn(null);
        $this->em->method('getRepository')->willReturn($mockRepo);

        $importer = $this->createMock(ProductImporterInterface::class);

        $controller = $this->getMockBuilder(ProductController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Product not found'], 404)
            ->willReturn(new JsonResponse(['error' => 'Product not found'], 404));

        $response = $controller->delete('missing_key', $this->em, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteCallsImporterRemove(): void
    {
        $product = new Product();

        $mockRepo = $this->createMock(EntityRepository::class);
        $mockRepo->method('findOneBy')->willReturn($product);
        $this->em->method('getRepository')->willReturn($mockRepo);

        $importer = $this->createMock(ProductImporterInterface::class);
        $importer->expects($this->once())->method('removeProduct')->with($product);

        $controller = $this->getMockBuilder(ProductController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->method('json')->willReturnCallback(function ($data) {
            return new JsonResponse($data);
        });

        $response = $controller->delete('key', $this->em, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $payload = json_decode($response->getContent(), true);
        $this->assertEquals('Product deleted', $payload['status']);
    }
}
