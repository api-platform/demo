<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function getAverageRating(Book $book): ?int
    {
        $rating = $this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->where('r.book = :book')->setParameter('book', $book)
            ->getQuery()->getSingleScalarResult()
        ;

        return $rating ? (int) $rating : null;
    }

    public function findHighestReviewDay(): ?string
    {
        $qb = $this->createQueryBuilder('r')
            ->select('DATE(r.publishedAt) as dayDate, COUNT(r.id) as review_count')
            ->groupBy('dayDate')
            ->orderBy('review_count', 'DESC')
            ->addOrderBy('dayDate', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        $review_count = $result['review_count'];
        return $result['dayDate'] . " with $review_count reviews";
    }

    public function findHighestReviewMonth(): ?string
    {
        $qb = $this->createQueryBuilder('r')
            ->select('SUBSTRING(r.publishedAt) as monthDate, COUNT(r.id) as review_count')
            ->groupBy('monthDate')
            ->orderBy('review_count', 'DESC')
            ->addOrderBy('monthDate', 'DESC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();
        $review_count = $result['review_count'];
        return $result['monthDate'] . " with $review_count reviews";
    }

    public function save(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Review $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
