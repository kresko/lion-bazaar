<?php

namespace App\Tests\Unit\Service\Importer\Cms;

use App\Entity\CmsContentItem;
use App\Entity\CmsBlock;
use App\Service\Importer\Cms\CmsContentItemImporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CmsContentItemImporterTest extends TestCase
{
    private EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $em;
    private CmsContentItemImporter $importer;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->importer = new CmsContentItemImporter($this->em);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $r = new \ReflectionClass($entity);
        $p = $r->getProperty('id');
        $p->setAccessible(true);
        $p->setValue($entity, $id);
    }

    public function testImportCmsContentItemCreatesNewAndPersists(): void
    {
        $data = [CmsContentItemImporter::CONTENT_ITEM => [
            ['key' => 'hero_content', 'name' => 'Hero Content', 'parent_key' => 'hero_block', 'data' => ['text' => 'Welcome']]
        ]];

        $cmsBlock = new CmsBlock();
        $this->setEntityId($cmsBlock, 1);
        $cmsBlock->setKey('hero_block')->setName('Hero Block')->setCreatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTime());

        $cmsContentItemRepo = $this->createMock(EntityRepository::class);
        $cmsContentItemRepo->method('findOneBy')->willReturn(null);

        $cmsBlockRepo = $this->createMock(EntityRepository::class);
        $cmsBlockRepo->method('findOneBy')->willReturn($cmsBlock);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($cmsContentItemRepo, $cmsBlockRepo) {
            if ($class === CmsContentItem::class) {
                return $cmsContentItemRepo;
            }
            if ($class === CmsBlock::class) {
                return $cmsBlockRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($ent) use (&$persisted) {
            $persisted[] = $ent;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCmsContentItem($data);

        $this->assertCount(1, $result['created']);
        $this->assertCount(0, $result['updated']);
        $this->assertCount(2, $persisted);
        $this->assertInstanceOf(CmsContentItem::class, $persisted[0]);
        $this->assertInstanceOf(CmsBlock::class, $persisted[1]);
    }

    public function testImportCmsContentItemUpdatesExisting(): void
    {
        $data = [CmsContentItemImporter::CONTENT_ITEM => [
            ['key' => 'hero_content', 'name' => 'Hero Content Updated', 'parent_key' => 'hero_block', 'data' => ['text' => 'Welcome back']]
        ]];

        $existingItem = new CmsContentItem();
        $this->setEntityId($existingItem, 10);
        $existingItem->setKey('hero_content')->setName('Hero Content')->setCreatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTime());

        $cmsBlock = new CmsBlock();
        $this->setEntityId($cmsBlock, 1);
        $cmsBlock->setKey('hero_block')->setName('Hero Block')->setCreatedAt(new \DateTimeImmutable())->setUpdatedAt(new \DateTime());

        $cmsContentItemRepo = $this->createMock(EntityRepository::class);
        $cmsContentItemRepo->method('findOneBy')->willReturn($existingItem);

        $cmsBlockRepo = $this->createMock(EntityRepository::class);
        $cmsBlockRepo->method('findOneBy')->willReturn($cmsBlock);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($cmsContentItemRepo, $cmsBlockRepo) {
            if ($class === CmsContentItem::class) {
                return $cmsContentItemRepo;
            }
            if ($class === CmsBlock::class) {
                return $cmsBlockRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->exactly(2))->method('persist')->willReturnCallback(function ($ent) use (&$persisted) {
            $persisted[] = $ent;
        });
        $this->em->expects($this->once())->method('flush');

        $result = $this->importer->importCmsContentItem($data);

        $this->assertCount(0, $result['created']);
        $this->assertCount(1, $result['updated']);
        $this->assertCount(2, $persisted);
        $this->assertSame($existingItem, $persisted[0]);
    }
}
