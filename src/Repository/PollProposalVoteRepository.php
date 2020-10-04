<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\PollProposalVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PollProposalVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method PollProposalVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method PollProposalVote[]    findAll()
 * @method PollProposalVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollProposalVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollProposalVote::class);
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
    //  * @return PollProposalVote[] Returns an array of PollProposalVote objects
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
    public function findOneBySomeField($value): ?PollProposalVote
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
