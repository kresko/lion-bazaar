<?php

namespace App\Tests\Unit\Command;

use App\Command\BuildCmsStructureCommand;
use App\Entity\CmsSlot;
use App\Entity\CmsBlock;
use App\Entity\CmsContentItem;
use App\Entity\CmsStorage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCmsStructureCommandTest extends TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var BuildCmsStructureCommand */
    private $command;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->command = new BuildCmsStructureCommand($this->em);
    }

    /**
     * @param object $entity
     * @param int $id
     *
     * @return void
     */
    private function setEntityId($entity, $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }

    /**
     * @param object $entity
     * @param string $propertyName
     * @param mixed $value
     *
     * @return void
     */
    private function setEntityProperty($entity, $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($entity, $value);
    }

    private function executeCommand(InputInterface $input, OutputInterface $output): int
    {
        $reflection = new \ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        return $reflection->invoke($this->command, $input, $output);
    }

    public function testExecuteBuildsCmsStructureSuccessfully(): void
    {
        // Create CMS slot
        $slot = new CmsSlot();
        $this->setEntityId($slot, 1);
        $this->setEntityProperty($slot, 'key', 'hero');
        $this->setEntityProperty($slot, 'name', 'Hero');
        $this->setEntityProperty($slot, 'created_at', new \DateTimeImmutable());
        $this->setEntityProperty($slot, 'updated_at', new \DateTime());

        $slotRepo = $this->createMock(EntityRepository::class);
        $slotRepo->method('findOneBy')->willReturn($slot);

        $blockRepo = $this->createMock(EntityRepository::class);
        $blockRepo->method('findAll')->willReturn([]);

        $contentItemRepo = $this->createMock(EntityRepository::class);
        $contentItemRepo->method('findAll')->willReturn([]);

        $cmsStorageRepo = $this->createMock(EntityRepository::class);
        $cmsStorageRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($slotRepo, $blockRepo, $contentItemRepo, $cmsStorageRepo) {
            if ($class === CmsSlot::class) {
                return $slotRepo;
            } elseif ($class === CmsBlock::class) {
                return $blockRepo;
            } elseif ($class === CmsContentItem::class) {
                return $contentItemRepo;
            } elseif ($class === CmsStorage::class) {
                return $cmsStorageRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush');

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('slot')->willReturn('hero');
        $output = $this->createMock(OutputInterface::class);

        $result = $this->executeCommand($input, $output);

        $this->assertEquals(0, $result);
        $this->assertCount(1, $persisted);
        $this->assertInstanceOf(CmsStorage::class, $persisted[0]);
    }

    public function testExecuteBuildsCorrectCmsHierarchy(): void
    {
        // Create CMS content item
        $contentItem = new CmsContentItem();
        $this->setEntityId($contentItem, 1);
        $this->setEntityProperty($contentItem, 'key', 'content_1');
        $this->setEntityProperty($contentItem, 'name', 'Content');
        $this->setEntityProperty($contentItem, 'created_at', new \DateTimeImmutable());
        $this->setEntityProperty($contentItem, 'updated_at', new \DateTime());

        // Create CMS block
        $block = new CmsBlock();
        $this->setEntityId($block, 1);
        $this->setEntityProperty($block, 'key', 'block_1');
        $this->setEntityProperty($block, 'name', 'Block 1');
        $this->setEntityProperty($block, 'created_at', new \DateTimeImmutable());
        $this->setEntityProperty($block, 'updated_at', new \DateTime());

        // Create CMS slot
        $slot = new CmsSlot();
        $this->setEntityId($slot, 1);
        $this->setEntityProperty($slot, 'key', 'hero');
        $this->setEntityProperty($slot, 'name', 'Hero');
        $this->setEntityProperty($slot, 'created_at', new \DateTimeImmutable());
        $this->setEntityProperty($slot, 'updated_at', new \DateTime());

        $slotRepo = $this->createMock(EntityRepository::class);
        $slotRepo->method('findOneBy')->willReturn($slot);

        $blockRepo = $this->createMock(EntityRepository::class);
        $blockRepo->method('findAll')->willReturn([$block]);

        $contentItemRepo = $this->createMock(EntityRepository::class);
        $contentItemRepo->method('findAll')->willReturn([$contentItem]);

        $cmsStorageRepo = $this->createMock(EntityRepository::class);
        $cmsStorageRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($slotRepo, $blockRepo, $contentItemRepo, $cmsStorageRepo) {
            if ($class === CmsSlot::class) {
                return $slotRepo;
            } elseif ($class === CmsBlock::class) {
                return $blockRepo;
            } elseif ($class === CmsContentItem::class) {
                return $contentItemRepo;
            } elseif ($class === CmsStorage::class) {
                return $cmsStorageRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush');

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('slot')->willReturn('hero');
        $output = $this->createMock(OutputInterface::class);

        $this->executeCommand($input, $output);

        // Verify that a CmsStorage entity was persisted
        $this->assertCount(1, $persisted);
        $cmsStorage = $persisted[0];
        $this->assertInstanceOf(CmsStorage::class, $cmsStorage);
    }

    public function testExecuteUpdatesExistingCmsStorage(): void
    {
        $slot = new CmsSlot();
        $this->setEntityId($slot, 1);
        $this->setEntityProperty($slot, 'key', 'hero');
        $this->setEntityProperty($slot, 'name', 'Hero');
        $this->setEntityProperty($slot, 'created_at', new \DateTimeImmutable());
        $this->setEntityProperty($slot, 'updated_at', new \DateTime());

        $slotRepo = $this->createMock(EntityRepository::class);
        $slotRepo->method('findOneBy')->willReturn($slot);

        $blockRepo = $this->createMock(EntityRepository::class);
        $blockRepo->method('findAll')->willReturn([]);

        $contentItemRepo = $this->createMock(EntityRepository::class);
        $contentItemRepo->method('findAll')->willReturn([]);

        $existingStorage = new CmsStorage();
        $this->setEntityProperty($existingStorage, 'created_at', new \DateTimeImmutable());

        $cmsStorageRepo = $this->createMock(EntityRepository::class);
        $cmsStorageRepo->method('findOneBy')->willReturn($existingStorage);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($slotRepo, $blockRepo, $contentItemRepo, $cmsStorageRepo) {
            if ($class === CmsSlot::class) {
                return $slotRepo;
            } elseif ($class === CmsBlock::class) {
                return $blockRepo;
            } elseif ($class === CmsContentItem::class) {
                return $contentItemRepo;
            } elseif ($class === CmsStorage::class) {
                return $cmsStorageRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush');

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('slot')->willReturn('hero');
        $output = $this->createMock(OutputInterface::class);

        $result = $this->executeCommand($input, $output);

        $this->assertEquals(0, $result);
        $this->assertSame($existingStorage, $persisted[0]);
    }
}
