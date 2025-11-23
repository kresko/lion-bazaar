<?php

namespace App\Tests\Unit\Controller\Cms;

use App\Controller\Cms\CmsBlockController;
use App\Entity\CmsBlock;
use App\Service\Importer\Cms\CmsBlockImporterInterface;
use App\Service\Validator\Cms\CmsBlockValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CmsBlockControllerTest extends TestCase
{
    public function testCreateReturns400OnInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], 'not json');

        $validator = $this->createMock(CmsBlockValidator::class);
        $importer = $this->createMock(CmsBlockImporterInterface::class);

        $controller = $this->getMockBuilder(CmsBlockController::class)
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
        $payload = ['blocks' => []];
        $request = new Request([], [], [], [], [], [], json_encode($payload));

        $validator = $this->createMock(CmsBlockValidator::class);
        $validator->method('validate')->willReturn($payload);

        $importer = $this->createMock(CmsBlockImporterInterface::class);
        $importer->method('importCmsBlock')->willReturn(['created' => ['a'], 'updated' => []]);

        $controller = $this->getMockBuilder(CmsBlockController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($arg) {
                return isset($arg['status']) && isset($arg['created']) && isset($arg['updated']);
            }))
            ->willReturn(new JsonResponse(['status' => 'Cms block created'], 200));

        $response = $controller->create($request, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testDeleteReturns404WhenNotFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em->method('getRepository')->willReturn($repo);

        $controller = $this->getMockBuilder(CmsBlockController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Cms block not found'], 404)
            ->willReturn(new JsonResponse(['error' => 'Cms block not found'], 404));

        $response = $controller->delete('missing-key', $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteRemovesBlockWhenFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EntityRepository::class);

        $block = new CmsBlock();
        $reflection = new \ReflectionClass($block);
        $prop = $reflection->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($block, 5);

        $repo->method('findOneBy')->willReturn($block);
        $em->method('getRepository')->willReturn($repo);

        $em->expects($this->once())->method('remove')->with($block);
        $em->expects($this->once())->method('flush');

        $controller = $this->getMockBuilder(CmsBlockController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($arg) {
                return isset($arg['status']) && $arg['key'] === 'any-key';
            }))
            ->willReturn(new JsonResponse(['status' => 'Cms block deleted', 'key' => 'any-key'], 200));

        $response = $controller->delete('any-key', $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
