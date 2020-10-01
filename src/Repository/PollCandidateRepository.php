<?php

namespace App\Repository;

use App\Entity\PollCandidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PollCandidate|null find($id, $lockMode = null, $lockVersion = null)
 * @method PollCandidate|null findOneBy(array $criteria, array $orderBy = null)
 * @method PollCandidate[]    findAll()
 * @method PollCandidate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollCandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollCandidate::class);
    }

    // /**
    //  * @return PollCandidate[] Returns an array of PollCandidate objects
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
    public function findOneBySomeField($value): ?PollCandidate
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
