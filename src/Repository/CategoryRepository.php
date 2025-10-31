<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findDescendantCategoryKeys(string $rootKey): array
    {
        $keys = [];
        $queue = [$rootKey];
        // we'll skip returning the root itself here (caller can include it)
        // use a visited set to avoid cycles
        $visited = [];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;

            $rows = $this->createQueryBuilder('c')
                ->select('c.category_key AS key')
                ->where('c.parent_category_key = :parent')
                ->setParameter('parent', $current)
                ->getQuery()
                ->getArrayResult();

            foreach ($rows as $r) {
                $childKey = $r['key'];
                // avoid duplicates/cycles
                if (!isset($visited[$childKey])) {
                    $keys[] = $childKey;
                    $queue[] = $childKey;
                }
            }
        }

        return $keys;
    }
}
