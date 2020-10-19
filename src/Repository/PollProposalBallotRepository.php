<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\Poll\Proposal\Ballot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Ballot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ballot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ballot[]    findAll()
 * @method Ballot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollProposalBallotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ballot::class);
    }


    public function countVotesOnPoll(Poll $poll)
    {
        $count = 0;
        foreach ($poll->getProposals() as $proposal) {
            $count += $this->count([
                'proposal' => $proposal->getId(),
            ]);
        }
        return $count;
    }

    // /**
    //  * @return Ballot[] Returns an array of Ballot objects
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
    public function findOneBySomeField($value): ?Ballot
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
