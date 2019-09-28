<?php

namespace App\Repository;

use App\Entity\LimajuPollOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LimajuPollOption|null find($id, $lockMode = null, $lockVersion = null)
 * @method LimajuPollOption|null findOneBy(array $criteria, array $orderBy = null)
 * @method LimajuPollOption[]    findAll()
 * @method LimajuPollOption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LimajuPollOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LimajuPollOption::class);
    }

    // /**
    //  * @return LimajuPollOption[] Returns an array of LimajuPollOption objects
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
    public function findOneBySomeField($value): ?LimajuPollOption
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
