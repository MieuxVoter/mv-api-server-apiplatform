<?php

namespace App\Repository;

use App\Entity\PollProposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PollProposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method PollProposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method PollProposal[]    findAll()
 * @method PollProposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollProposalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollProposal::class);
    }

    // /**
    //  * @return PollProposal[] Returns an array of PollProposal objects
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
    public function findOneBySomeField($value): ?PollProposal
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
