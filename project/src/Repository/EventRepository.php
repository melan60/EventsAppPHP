<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByFilters($title, $date, $placesRemaining, $isPublic)
    {
        $qb = $this->createQueryBuilder('e');

        if ($title) {
            $qb->andWhere('e.title LIKE :title')
               ->setParameter('title', '%' . $title . '%');
        }

        if ($date) {
            $qb->andWhere('e.date = :date')
               ->setParameter('date', new \DateTime($date));
        }

        if ($placesRemaining) {
            $qb->leftJoin('e.participants', 'p')
               ->groupBy('e.id')
               ->having('COUNT(p.id) < e.participants_number');
        }

        if ($isPublic !== null  && $isPublic !== '') {
            $qb->andWhere('e.public = :public')
               ->setParameter('public', $isPublic === '1');
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
