<?php

namespace App\Repository;

use App\Entity\NavigationTree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NavigationTree>
 */
class NavigationTreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NavigationTree::class);
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getTreeJson(): ?array
    {
        $navigationTree = $this->findOneBy([]);

        if (!$navigationTree) {
            return null;
        }

        return $navigationTree->getTreeJson();
    }
}
