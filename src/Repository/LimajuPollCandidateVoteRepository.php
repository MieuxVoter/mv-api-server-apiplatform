<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\LimajuPollCandidateVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LimajuPollCandidateVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method LimajuPollCandidateVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method LimajuPollCandidateVote[]    findAll()
 * @method LimajuPollCandidateVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LimajuPollCandidateVoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LimajuPollCandidateVote::class);
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
    //  * @return LimajuPollCandidateVote[] Returns an array of LimajuPollCandidateVote objects
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
    public function findOneBySomeField($value): ?LimajuPollCandidateVote
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
