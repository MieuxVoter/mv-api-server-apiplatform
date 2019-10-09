<?php

namespace App\Repository;

use App\Entity\LimajuPollCandidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LimajuPollCandidate|null find($id, $lockMode = null, $lockVersion = null)
 * @method LimajuPollCandidate|null findOneBy(array $criteria, array $orderBy = null)
 * @method LimajuPollCandidate[]    findAll()
 * @method LimajuPollCandidate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LimajuPollCandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LimajuPollCandidate::class);
    }

    // /**
    //  * @return LimajuPollCandidate[] Returns an array of LimajuPollCandidate objects
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
    public function findOneBySomeField($value): ?LimajuPollCandidate
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
