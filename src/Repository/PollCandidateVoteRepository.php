<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\PollCandidateVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PollCandidateVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method PollCandidateVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method PollCandidateVote[]    findAll()
 * @method PollCandidateVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollCandidateVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollCandidateVote::class);
    }


    public function countVotesOnPoll(Poll $poll)
    {
        $count = 0;
        foreach ($poll->getCandidates() as $candidate) {
            $count += $this->count([
                'candidate' => $candidate->getId(),
            ]);
        }
        return $count;
    }

    // /**
    //  * @return PollCandidateVote[] Returns an array of PollCandidateVote objects
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
    public function findOneBySomeField($value): ?PollCandidateVote
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
