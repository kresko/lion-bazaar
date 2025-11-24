<?php

namespace App\Repository;

use App\Entity\NavigationTree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NavigationTreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NavigationTree::class);
    }

    public function getTreeJson(): ?array
    {
        $navigationTree = $this->findOneBy([]);

        if (!$navigationTree) {
            return null;
        }

        $json = $navigationTree->getTreeJson();

        return is_string($json) ? json_decode($json, true) : $json;
    }
}
