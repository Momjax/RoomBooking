<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Vérifie si une salle est disponible pour un créneau donné
     */
    public function isRoomAvailable(int $roomId, \DateTimeInterface $start, \DateTimeInterface $end, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.room = :roomId')
            ->andWhere('r.reservationStart < :end')
            ->andWhere('r.reservationEnd > :start')
            ->setParameter('roomId', $roomId)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($excludeId) {
            $qb->andWhere('r.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return count($qb->getQuery()->getResult()) === 0;
    }
}
