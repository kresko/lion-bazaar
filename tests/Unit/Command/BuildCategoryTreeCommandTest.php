<?php

namespace App\Tests\Unit\Command;

use App\Command\BuildCategoryTreeCommand;
use App\Entity\Category;
use App\Entity\CategoryTree;
use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCategoryTreeCommandTest extends TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var BuildCategoryTreeCommand */
    private $command;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->command = new BuildCategoryTreeCommand($this->em);
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

    private function executeCommand(InputInterface $input, OutputInterface $output): int
    {
        $reflection = new \ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        return $reflection->invoke($this->command, $input, $output);
    }

    public function testExecuteBuildsTreeAndPersistsSuccessfully(): void
    {
        // Create test categories
        $electronics = new Category();
        $this->setEntityId($electronics, 1);
        $electronics->setName('Electronics');
        $electronics->setCategoryKey('electronics');
        $electronics->setNodeOrder(1);
        $electronics->setIsRoot(true);
        $electronics->setParentCategoryKey(null);
        $electronics->setCreatedAtValue();
        $electronics->setUpdatedAtValue();

        $tablets = new Category();
        $this->setEntityId($tablets, 2);
        $tablets->setName('Tablets');
        $tablets->setCategoryKey('tablets');
        $tablets->setNodeOrder(2);
        $tablets->setIsRoot(false);
        $tablets->setParentCategoryKey('electronics');
        $tablets->setCreatedAtValue();
        $tablets->setUpdatedAtValue();

        // Create test URLs
        $electronicsUrl = new Url();
        $electronicsUrl->setUrl('/electronics');
        $tabletsUrl = new Url();
        $tabletsUrl->setUrl('/electronics/tablets');

        $categoryRepo = $this->createMock(EntityRepository::class);
        $categoryRepo->method('findAll')->willReturn([$electronics, $tablets]);

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturnCallback(function ($criteria) use ($electronics, $tablets, $electronicsUrl, $tabletsUrl) {
            if ($criteria['category'] === $electronics) {
                return $electronicsUrl;
            }
            if ($criteria['category'] === $tablets) {
                return $tabletsUrl;
            }
            return null;
        });

        $categoryTreeRepo = $this->createMock(EntityRepository::class);
        $categoryTreeRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($categoryRepo, $urlRepo, $categoryTreeRepo) {
            if ($class === Category::class) {
                return $categoryRepo;
            } elseif ($class === Url::class) {
                return $urlRepo;
            } elseif ($class === CategoryTree::class) {
                return $categoryTreeRepo;
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
        $this->assertInstanceOf(CategoryTree::class, $persisted[0]);
    }

    public function testExecuteBuildsCorrectTreeHierarchy(): void
    {
        // Create test categories with parent-child relationship
        $root = new Category();
        $this->setEntityId($root, 1);
        $root->setName('Root');
        $root->setCategoryKey('root');
        $root->setNodeOrder(1);
        $root->setIsRoot(true);
        $root->setParentCategoryKey(null);
        $root->setCreatedAtValue();
        $root->setUpdatedAtValue();

        $child = new Category();
        $this->setEntityId($child, 2);
        $child->setName('Child');
        $child->setCategoryKey('child');
        $child->setNodeOrder(1);
        $child->setIsRoot(false);
        $child->setParentCategoryKey('root');
        $child->setCreatedAtValue();
        $child->setUpdatedAtValue();

        $categoryRepo = $this->createMock(EntityRepository::class);
        $categoryRepo->method('findAll')->willReturn([$root, $child]);

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturn(null);

        $categoryTreeRepo = $this->createMock(EntityRepository::class);
        $categoryTreeRepo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($categoryRepo, $urlRepo, $categoryTreeRepo) {
            if ($class === Category::class) {
                return $categoryRepo;
            } elseif ($class === Url::class) {
                return $urlRepo;
            } elseif ($class === CategoryTree::class) {
                return $categoryTreeRepo;
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

        // Get the persisted CategoryTree
        $categoryTree = $persisted[0];
        $tree = $categoryTree->getTreeJson();

        // Verify structure
        $this->assertCount(1, $tree);
        $this->assertEquals('Root', $tree[0]['name']);
        $this->assertCount(1, $tree[0]['children']);
        $this->assertEquals('Child', $tree[0]['children'][0]['name']);
    }

    public function testExecuteUpdatesExistingCategoryTree(): void
    {
        $category = new Category();
        $this->setEntityId($category, 1);
        $category->setName('Test');
        $category->setCategoryKey('test');
        $category->setNodeOrder(1);
        $category->setIsRoot(true);
        $category->setParentCategoryKey(null);
        $category->setCreatedAtValue();
        $category->setUpdatedAtValue();

        $categoryRepo = $this->createMock(EntityRepository::class);
        $categoryRepo->method('findAll')->willReturn([$category]);

        $urlRepo = $this->createMock(EntityRepository::class);
        $urlRepo->method('findOneBy')->willReturn(null);

        $existingTree = new CategoryTree();
        $existingTree->setCreatedAtValue();

        $categoryTreeRepo = $this->createMock(EntityRepository::class);
        $categoryTreeRepo->method('findOneBy')->willReturn($existingTree);

        $this->em->method('getRepository')->willReturnCallback(function ($class) use ($categoryRepo, $urlRepo, $categoryTreeRepo) {
            if ($class === Category::class) {
                return $categoryRepo;
            } elseif ($class === Url::class) {
                return $urlRepo;
            } elseif ($class === CategoryTree::class) {
                return $categoryTreeRepo;
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
        $this->assertSame($existingTree, $persisted[0]);
    }
}
