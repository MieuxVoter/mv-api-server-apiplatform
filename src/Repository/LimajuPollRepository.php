<?php

namespace App\Repository;

use App\Entity\LimajuPoll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LimajuPoll|null find($id, $lockMode = null, $lockVersion = null)
 * @method LimajuPoll|null findOneBy(array $criteria, array $orderBy = null)
 * @method LimajuPoll[]    findAll()
 * @method LimajuPoll[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LimajuPollRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LimajuPoll::class);
    }

    // /**
    //  * @return LimajuPoll[] Returns an array of LimajuPoll objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LimajuPoll
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
