<?php

namespace App\Tests\Unit\Service\Builder;

use App\Entity\Category;
use App\Service\Builder\Url\CategoryUrlBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class CategoryUrlBuilderTest extends TestCase
{
    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
    }

    public function testBuildUrlFromCategoryReturnsNullForRoot(): void
    {
        $category = new Category();
        $category->setName('Electronics');
        $category->setParentCategoryKey(null);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn(null);

        $this->em->method('getRepository')->willReturn($repo);

        $builder = new CategoryUrlBuilder($this->em);

        $this->assertNull($builder->buildUrlFromCategory($category));
    }

    public function testBuildUrlFromCategoryBuildsFullPath(): void
    {
        $child = new Category();
        $child->setName('Tablets');
        $child->setParentCategoryKey('electronics');

        $parent = new Category();
        $parent->setName('Electronics');
        $parent->setParentCategoryKey(null);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')
            ->willReturnOnConsecutiveCalls($parent, null);

        $this->em->method('getRepository')->willReturn($repo);

        $builder = new CategoryUrlBuilder($this->em);

        $url = $builder->buildUrlFromCategory($child);

        $this->assertStringContainsString('Tablets', $url);
    }
}
