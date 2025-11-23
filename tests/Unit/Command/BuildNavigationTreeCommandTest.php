<?php

namespace App\Tests\Unit\Command;

use App\Command\BuildNavigationTreeCommand;
use App\Entity\Category;
use App\Entity\Url;
use App\Entity\NavigationTree;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildNavigationTreeCommandTest extends TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var BuildNavigationTreeCommand */
    private $command;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->command = new BuildNavigationTreeCommand($this->em);
    }

    /**
     * @param Category $entity
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

    private function executeCommand(InputInterface $input, OutputInterface $output): int
    {
        $reflection = new \ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        return $reflection->invoke($this->command, $input, $output);
    }

    public function testExecuteBuildsNavigationTreeSuccessfully(): void
    {
        // Create root category
        $root = new Category();
        $this->setEntityId($root, 1);
        $root->setName('Navigation Root');
        $root->setCategoryKey('nav_root');
        $root->setNodeOrder(1);
        $root->setIsRoot(true);
        $root->setParentCategoryKey(null);
        $root->setCreatedAtValue();
        $root->setUpdatedAtValue();

        // Create child category
        $child = new Category();
        $this->setEntityId($child, 2);
        $child->setName('Child');
        $child->setCategoryKey('child');
        $child->setNodeOrder(1);
        $child->setIsRoot(false);
        $child->setParentCategoryKey('nav_root');
        $child->setCreatedAtValue();
        $child->setUpdatedAtValue();

        $categoryRepo = $this->createMock(EntityRepository::class);
        $categoryRepo->method('findAll')->willReturn([$root, $child]);

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturn(null);

        $navTreeRepo = $this->createMock(EntityRepository::class);
        $navTreeRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($categoryRepo, $urlRepo, $navTreeRepo) {
            if ($class === Category::class) {
                return $categoryRepo;
            } elseif ($class === Url::class) {
                return $urlRepo;
            } elseif ($class === NavigationTree::class) {
                return $navTreeRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush');

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $result = $this->executeCommand($input, $output);

        $this->assertEquals(0, $result);
        $this->assertCount(1, $persisted);
        $this->assertInstanceOf(NavigationTree::class, $persisted[0]);
    }

    public function testExecuteIncludesRootCategoriesInNavigation(): void
    {
        // Create root category (is_root = true)
        $root = new Category();
        $this->setEntityId($root, 1);
        $root->setName('Root');
        $root->setCategoryKey('root');
        $root->setNodeOrder(1);
        $root->setIsRoot(true);
        $root->setParentCategoryKey(null);
        $root->setCreatedAtValue();
        $root->setUpdatedAtValue();

        // Create non-root category (not included)
        $nonRoot = new Category();
        $this->setEntityId($nonRoot, 2);
        $nonRoot->setName('Non Root');
        $nonRoot->setCategoryKey('non_root');
        $nonRoot->setNodeOrder(2);
        $nonRoot->setIsRoot(false);
        $nonRoot->setParentCategoryKey('other');
        $nonRoot->setCreatedAtValue();
        $nonRoot->setUpdatedAtValue();

        $categoryRepo = $this->createMock(EntityRepository::class);
        $categoryRepo->method('findAll')->willReturn([$root, $nonRoot]);

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturn(null);

        $navTreeRepo = $this->createMock(EntityRepository::class);
        $navTreeRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($categoryRepo, $urlRepo, $navTreeRepo) {
            if ($class === Category::class) {
                return $categoryRepo;
            } elseif ($class === Url::class) {
                return $urlRepo;
            } elseif ($class === NavigationTree::class) {
                return $navTreeRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush');

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->executeCommand($input, $output);

        $navTree = $persisted[0];
        $tree = $navTree->getTreeJson();

        // Should only have root category (root, not non_root)
        $this->assertCount(1, $tree);
        $this->assertEquals('Root', $tree[0]['name']);
    }

    public function testExecuteUpdatesExistingNavigationTree(): void
    {
        $root = new Category();
        $this->setEntityId($root, 1);
        $root->setName('Root');
        $root->setCategoryKey('root');
        $root->setNodeOrder(1);
        $root->setIsRoot(true);
        $root->setParentCategoryKey(null);
        $root->setCreatedAtValue();
        $root->setUpdatedAtValue();

        $categoryRepo = $this->createMock(EntityRepository::class);
        $categoryRepo->method('findAll')->willReturn([$root]);

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturn(null);

        $existingNavTree = new NavigationTree();
        $existingNavTree->setCreatedAtValue();

        $navTreeRepo = $this->createMock(EntityRepository::class);
        $navTreeRepo->method('findOneBy')->willReturn($existingNavTree);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($categoryRepo, $urlRepo, $navTreeRepo) {
            if ($class === Category::class) {
                return $categoryRepo;
            } elseif ($class === Url::class) {
                return $urlRepo;
            } elseif ($class === NavigationTree::class) {
                return $navTreeRepo;
            }
            return null;
        });

        $persisted = [];
        $this->em->expects($this->once())->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });
        $this->em->expects($this->once())->method('flush');

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $result = $this->executeCommand($input, $output);

        $this->assertEquals(0, $result);
        $this->assertSame($existingNavTree, $persisted[0]);
    }
}
