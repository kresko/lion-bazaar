<?php

namespace App\Tests\Unit\Service\Importer\Cms;

use App\Entity\CmsSlot;
use App\Service\Importer\Cms\CmsSlotImporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CmsSlotImporterTest extends TestCase
{
    private EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $em;
    private CmsSlotImporter $importer;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->importer = new CmsSlotImporter($this->em);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $r = new \ReflectionClass($entity);
        $p = $r->getProperty('id');
        $p->setAccessible(true);
        $p->setValue($entity, $id);
    }

    public function testImportCmsSlotCreatesNewAndPersists(): void
    {
        $data = [CmsSlotImporter::SLOTS => [
            ['key' => 'hero', 'name' => 'Hero Slot']
        ]];

        $cmsSlotRepo = $this->createMock(EntityRepository::class);
        $cmsSlotRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($cmsSlotRepo);

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($ent) use (&$persisted) {
            $persisted[] = $ent;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCmsSlot($data);

        $this->assertIsArray($result);
        $this->assertCount(1, $result['created']);
        $this->assertCount(0, $result['updated']);
        $this->assertCount(1, $persisted);
        $this->assertInstanceOf(CmsSlot::class, $persisted[0]);
        $this->assertEquals('hero', $persisted[0]->getKey());
        $this->assertEquals('Hero Slot', $persisted[0]->getName());
    }

    public function testImportCmsSlotUpdatesExisting(): void
    {
        $data = [CmsSlotImporter::SLOTS => [
            ['key' => 'hero', 'name' => 'Hero Slot Updated']
        ]];

        $existingSlot = new CmsSlot();
        $this->setEntityId($existingSlot, 5);
        $existingSlot->setKey('hero')->setName('Hero Slot')->setCreatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTime());

        $cmsSlotRepo = $this->createMock(EntityRepository::class);
        $cmsSlotRepo->method('findOneBy')->willReturn($existingSlot);

        $this->em->method('getRepository')->willReturn($cmsSlotRepo);

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($ent) use (&$persisted) {
            $persisted[] = $ent;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCmsSlot($data);

        $this->assertIsArray($result);
        $this->assertCount(0, $result['created']);
        $this->assertCount(1, $result['updated']);
        $this->assertCount(1, $persisted);
        $this->assertSame($existingSlot, $persisted[0]);
    }

    public function testImportMultipleCmsSlots(): void
    {
        $data = [CmsSlotImporter::SLOTS => [
            ['key' => 'hero', 'name' => 'Hero Slot'],
            ['key' => 'footer', 'name' => 'Footer Slot']
        ]];

        $cmsSlotRepo = $this->createMock(EntityRepository::class);
        $cmsSlotRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($cmsSlotRepo);

        $persisted = [];
        $this->em->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($ent) use (&$persisted) {
            $persisted[] = $ent;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCmsSlot($data);

        $this->assertIsArray($result);
        $this->assertCount(2, $result['created']);
        $this->assertCount(0, $result['updated']);
        $this->assertCount(2, $persisted);
        $this->assertEquals('hero', $persisted[0]->getKey());
        $this->assertEquals('footer', $persisted[1]->getKey());
    }
}
