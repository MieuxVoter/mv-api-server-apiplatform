<?php

namespace App\Repository;

use App\Entity\PollGrade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PollGrade|null find($id, $lockMode = null, $lockVersion = null)
 * @method PollGrade|null findOneBy(array $criteria, array $orderBy = null)
 * @method PollGrade[]    findAll()
 * @method PollGrade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollGradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollGrade::class);
    }

    // /**
    //  * @return PollGrade[] Returns an array of PollGrade objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PollGrade
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
