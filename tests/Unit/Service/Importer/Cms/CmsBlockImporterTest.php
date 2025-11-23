<?php

namespace App\Tests\Unit\Service\Importer\Cms;

use App\Entity\CmsBlock;
use App\Entity\CmsSlot;
use App\Service\Importer\Cms\CmsBlockImporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CmsBlockImporterTest extends TestCase
{
    private EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $em;
    private CmsBlockImporter $importer;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->importer = new CmsBlockImporter($this->em);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $r = new \ReflectionClass($entity);
        $p = $r->getProperty('id');
        $p->setAccessible(true);
        $p->setValue($entity, $id);
    }

    public function testImportCmsBlockCreatesNewAndPersists(): void
    {
        $data = [CmsBlockImporter::BLOCKS => [
            ['key' => 'hero_block', 'name' => 'Hero', 'parent_key' => 'hero_slot']
        ]];

        $cmsSlot = new CmsSlot();
        $this->setEntityId($cmsSlot, 1);
        $cmsSlot->setKey('hero_slot')->setName('Hero Slot')->setCreatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTime());

        $cmsBlockRepo = $this->createMock(EntityRepository::class);
        $cmsBlockRepo->method('findOneBy')->willReturn(null);

        $cmsSlotRepo = $this->createMock(EntityRepository::class);
        $cmsSlotRepo->method('findOneBy')->willReturn($cmsSlot);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($cmsBlockRepo, $cmsSlotRepo) {
            if ($class === CmsBlock::class) {
                return $cmsBlockRepo;
            }
            if ($class === CmsSlot::class) {
                return $cmsSlotRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($ent) use (&$persisted) {
            $persisted[] = $ent;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCmsBlock($data);

        $this->assertCount(1, $result['created']);
        $this->assertCount(0, $result['updated']);
        $this->assertCount(2, $persisted);
        $this->assertInstanceOf(CmsBlock::class, $persisted[0]);
        $this->assertInstanceOf(CmsSlot::class, $persisted[1]);
    }

    public function testImportCmsBlockUpdatesExisting(): void
    {
        $data = [CmsBlockImporter::BLOCKS => [
            ['key' => 'hero_block', 'name' => 'Hero Updated', 'parent_key' => 'hero_slot']
        ]];

        $existingBlock = new CmsBlock();
        $this->setEntityId($existingBlock, 10);
        $existingBlock->setKey('hero_block')->setName('Hero')->setCreatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTime());

        $cmsSlot = new CmsSlot();
        $this->setEntityId($cmsSlot, 1);
        $cmsSlot->setKey('hero_slot')->setName('Hero Slot')->setCreatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTime());

        $cmsBlockRepo = $this->createMock(EntityRepository::class);
        $cmsBlockRepo->method('findOneBy')->willReturn($existingBlock);

        $cmsSlotRepo = $this->createMock(EntityRepository::class);
        $cmsSlotRepo->method('findOneBy')->willReturn($cmsSlot);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($cmsBlockRepo, $cmsSlotRepo) {
            if ($class === CmsBlock::class) {
                return $cmsBlockRepo;
            }
            if ($class === CmsSlot::class) {
                return $cmsSlotRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($ent) use (&$persisted) {
            $persisted[] = $ent;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCmsBlock($data);

        $this->assertCount(0, $result['created']);
        $this->assertCount(1, $result['updated']);
        $this->assertCount(2, $persisted);
        $this->assertSame($existingBlock, $persisted[0]);
    }
}
