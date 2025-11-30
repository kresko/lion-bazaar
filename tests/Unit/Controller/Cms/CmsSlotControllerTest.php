<?php

namespace App\Tests\Unit\Controller\Cms;

use App\Controller\Cms\CmsSlotController;
use App\Entity\CmsSlot;
use App\Service\Importer\Cms\CmsSlotImporterInterface;
use App\Service\Validator\Cms\CmsSlotValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CmsSlotControllerTest extends TestCase
{
    public function testCreateReturns400OnInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], 'not json');

        $em = $this->createMock(EntityManagerInterface::class);
        $validator = $this->createMock(CmsSlotValidator::class);
        $importer = $this->createMock(CmsSlotImporterInterface::class);

        $controller = $this->getMockBuilder(CmsSlotController::class)
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
        $payload = ['slots' => []];
        $request = new Request([], [], [], [], [], [], json_encode($payload));

        $em = $this->createMock(EntityManagerInterface::class);
        $validator = $this->createMock(CmsSlotValidator::class);
        $validator->method('validate')->willReturn($payload);

        $importer = $this->createMock(CmsSlotImporterInterface::class);
        $importer->method('importCmsSlot')->willReturn(['created' => ['a'], 'updated' => []]);

        $controller = $this->getMockBuilder(CmsSlotController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($arg) {
                return isset($arg['status']) && isset($arg['created']) && isset($arg['updated']);
            }))
            ->willReturn(new JsonResponse(['status' => 'Cms slot created'], 200));

        $response = $controller->create($request, $em, $validator, $importer);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function testDeleteReturns404WhenNotFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $em->method('getRepository')->willReturn($repo);

        $controller = $this->getMockBuilder(CmsSlotController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with(['error' => 'Cms slot not found'], 404)
            ->willReturn(new JsonResponse(['error' => 'Cms slot not found'], 404));

        $response = $controller->delete('missing-key', $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDeleteRemovesSlotWhenFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EntityRepository::class);

        $slot = new CmsSlot();
        $reflection = new \ReflectionClass($slot);
        $prop = $reflection->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($slot, 3);

        $repo->method('findOneBy')->willReturn($slot);
        $em->method('getRepository')->willReturn($repo);

        $em->expects($this->once())->method('remove')->with($slot);
        $em->expects($this->once())->method('flush');

        $controller = $this->getMockBuilder(CmsSlotController::class)
            ->onlyMethods(['json'])
            ->getMock();

        $controller->expects($this->once())
            ->method('json')
            ->with($this->callback(function ($arg) {
                return isset($arg['status']) && $arg['key'] === 'hero-key';
            }))
            ->willReturn(new JsonResponse(['status' => 'Cms slot deleted', 'key' => 'hero-key'], 200));

        $response = $controller->delete('hero-key', $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
