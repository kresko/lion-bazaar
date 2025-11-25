<?php

namespace App\Tests\Unit\Controller\Cms;

use App\Controller\Cms\CmsContentItemController;
use App\Entity\CmsContentItem;
use App\Service\Importer\Cms\CmsContentItemImporterInterface;
use App\Service\Validator\Cms\CmsContentItemValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CmsContentItemControllerTest extends TestCase
{
    public function testCreateReturns400OnInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], 'not json');

        $em = $this->createMock(EntityManagerInterface::class);
        $validator = $this->createMock(CmsContentItemValidator::class);
        $importer = $this->createMock(CmsContentItemImporterInterface::class);

        $controller = $this->getMockBuilder(CmsContentItemController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Invalid JSON'], 400)
            ->willReturn(new JsonResponse(['error' => 'Invalid JSON'], 400));

        $response = $controller->create($request, $em, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateCallsValidatorAndImporter(): void
    {
        $payload = ['content-item' => []];
        $request = new Request([], [], [], [], [], [], json_encode($payload));

        $em = $this->createMock(EntityManagerInterface::class);
        $validator = $this->createMock(CmsContentItemValidator::class);
        $validator->method('validate')->willReturn($payload);

        $importer = $this->createMock(CmsContentItemImporterInterface::class);
        $importer->method('importCmsContentItem')->willReturn(['created' => ['a'], 'updated' => []]);

        $controller = $this->getMockBuilder(CmsContentItemController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($arg) {
                return isset($arg['status']) && isset($arg['created']) && isset($arg['updated']);
            }))
            ->willReturn(new JsonResponse(['status' => 'Cms content item created'], 200));

        $response = $controller->create($request, $em, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testDeleteReturns404WhenNotFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em->method('getRepository')->willReturn($repo);

        $controller = $this->getMockBuilder(CmsContentItemController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Content item not found'], 404)
            ->willReturn(new JsonResponse(['error' => 'Content item not found'], 404));

        $response = $controller->delete('missing-key', $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteRemovesItemWhenFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EntityRepository::class);

        $item = new CmsContentItem();
        $reflection = new \ReflectionClass($item);
        $prop = $reflection->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($item, 5);

        $repo->method('findOneBy')->willReturn($item);
        $em->method('getRepository')->willReturn($repo);

        $em->expects($this->once())->method('remove')->with($item);
        $em->expects($this->once())->method('flush');

        $controller = $this->getMockBuilder(CmsContentItemController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($arg) {
                return isset($arg['status']) && $arg['category_key'] === 'any-key';
            }))
            ->willReturn(new JsonResponse(['status' => 'Cms content item deleted', 'category_key' => 'any-key'], 200));

        $response = $controller->delete('any-key', $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
