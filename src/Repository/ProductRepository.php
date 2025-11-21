<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function fetchProductByCategoryKeys(array $keys): array
    {
        $products = $this->createQueryBuilder('p')
            ->where('p.category_key IN (:keys)')
            ->setParameter('keys', $keys)
            ->getQuery()
            ->getResult();

        return $products;
    }
}
