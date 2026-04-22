<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return list<Review>
     */
    public function findApprovedOrderedByNewest(): array
    {
        /** @var list<Review> $reviews */
        $reviews = $this->createQueryBuilder('r')
            ->andWhere('r.approuved_at IS NOT NULL')
            ->orderBy('r.approuved_at', 'DESC')
            ->addOrderBy('r.created_at', 'DESC')
            ->getQuery()
            ->getResult();

        return $reviews;
    }
}
