<?php

namespace App\Repository;

use App\Entity\LimajuPollOptionVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LimajuPollOptionVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method LimajuPollOptionVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method LimajuPollOptionVote[]    findAll()
 * @method LimajuPollOptionVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LimajuPollOptionVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LimajuPollOptionVote::class);
    }

    // /**
    //  * @return LimajuPollOptionVote[] Returns an array of LimajuPollOptionVote objects
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
    public function findOneBySomeField($value): ?LimajuPollOptionVote
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
