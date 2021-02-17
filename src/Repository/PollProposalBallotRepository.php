<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Proposal\Ballot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;


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


    /**
     * /!. Counts "duplicates" (ie. changes of minds) as well.  (since ballots are immutable)
     *
     * @param Poll $poll
     * @return int
     */
    public function countPollBallots(Poll $poll) : int
    {
//        $count = 0;
//        foreach ($poll->getProposals() as $proposal) {
//            $count += $this->count([
//                'proposal' => $proposal->getId(),
//            ]);
//        }
//        return $count;

        return $this->count([
            'proposal' => array_map(function(Proposal $proposal) {
                return $proposal->getId();
            }, $poll->getProposals()->toArray()),
        ]);
    }


    /**
     * Count the expressed ballots for each proposal.
     * Some proposals may have received more ballots than others,
     * since it is not mandatory for participants to give a grade to every proposal.
     *
     * This method helps when filling the blanks with the default grade, for example.
     *
     * @deprecated (and wrong for immutability)
     * @param Poll $poll
     * @return array of Proposal.uuid => amount
     */
    public function countParticipantsPerProposal(Poll $poll) : array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('uuid', 'uuid');
        $rsm->addScalarResult('amount', 'amount');

        $query = $this->getEntityManager()->createNativeQuery("
SELECT p.uuid, COUNT(*) AS amount
FROM Ballot b, Proposal p
WHERE p.id == b.proposal_id
GROUP BY b.proposal_id
", $rsm);

        $amountPerProposal = [];
        foreach ($query->getArrayResult() as $row) {
            $amountPerProposal[$row['uuid']] = (int) $row['amount'];
        }

        return $amountPerProposal;
    }


    /**
     * Returns the amount of judgments received of each grade (low to high), for each proposal of the $poll.
     *
     * These are the raw judgments from the database, so the sums may not match,
     * since judging all the proposals is not mandatory.
     * In other words, the "default grade" logic has not been applied yet.
     *
     * @param Poll $poll
     * @return array Associative array of proposal's UUID as string => [1, 4, 2, …]
     */
    public function getTallyPerProposal(Poll $poll)
    {
        $tallyPerProposal = array();
        $gs = $poll->getGradesInOrder();

//        $hashtable = [];
        foreach ($poll->getProposals() as $proposal) {

            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('uuid', 'uuid');
            $rsm->addScalarResult('amount', 'amount');

//            $query = $this->getEntityManager()->createNativeQuery("
//SELECT
//    COUNT(*) AS total
//FROM Ballot b
//WHERE b.proposal_id == :proposal_id
//GROUP BY b.participant HAVING FUN YOLO
//", $rsm);
//            $query->setParameter('proposal_id', $proposal->getId());
//            dump($query->getArrayResult());

//            $g = $gs[0];
            $qb = $this->createQueryBuilder('j')
                ->select("COUNT(j) total")
                ->andWhere('j.proposal = :proposal')
                ->leftJoin(
                    Ballot::class,
                    'jnw',
                    Join::WITH,
                    'j.proposal = jnw.proposal' .
                    ' AND ' .
                    'j.participant = jnw.participant' .
                    ' AND ' .
                    'j.id < jnw.id'
                )
                ->andWhere('jnw.proposal IS NULL')
                ->setParameter('proposal', $proposal)
                ->orderBy('j.id', 'ASC');
            foreach ($gs as $k => $grade) {
                $qb->addSelect("SUM(case when j.grade = :g$k then 1 else 0 end) grade$k");
                $qb->setParameter("g$k", $grade);
            }

//            dump($qb->getQuery()->getSQL());

            $test = $qb
                    ->getQuery()
                    ->getOneOrNullResult();

//            dump($proposal->getTitle(), $test);

            if (null == $test) {
                throw new \Exception("getTallyPerProposal() failed");
            }

            $proposalTally = array();

            foreach ($gs as $k => $grade) {
                $proposalTally[] = (int)$test['grade'.$k];
            }

//            foreach ($poll->getGradesInOrder() as $grade) {
//
//                $ballots = $this->findBy([
//                    'proposal' => $proposal->getId(),
//                    'grade' => $grade->getId(),
//                ], [
//                    'createdAt' => 'ASC',
//                ]);
//
//                $ballots = array_filter($ballots, function (Ballot $e) use (&$hashtable) {
//                    $h = $e->getProposal()->getUuid()->toString() . ":" .
//                        $e->getParticipant()->getUuid()->toString();
//                    if (in_array($h, $hashtable)) {
//                        return false;
//                    }
//                    $hashtable[] = $h;
//                    return true;
//                });
//
////                dump($hashtable);
//
//                // … filter out duplicate ballots, if they're immutable (scenario B)
//
//                $proposalTally[] = count($ballots);
//            }

            $tallyPerProposal[$proposal->getUuid()->toString()] = $proposalTally;




//            $votesCount = count($votes);
//            $maxVotesCount = max($maxVotesCount, $votesCount);
//            $gradesTally = array(); // grade_name => integer
//
//            usort($votes, function (Ballot $a, Ballot $b) use ($levelOfGrade) {
//                return (
//                    $levelOfGrade[$a->getGrade()->getUuid()->toString()]
//                    -
//                    $levelOfGrade[$b->getGrade()->getUuid()->toString()]
//                );
//            });
//
//            foreach ($levelOfGrade as $gradeToTallyUuid => $whoCares) {
//                $votesForMention = array_filter($votes, function (Ballot $v) use ($gradeToTallyUuid) {
//                    return $v->getGrade()->getUuid()->toString() === $gradeToTallyUuid;
//                });
//                $gradesTally[$gradeToTallyUuid] = count($votesForMention);
//            }

//            $proposalTally = new PollProposalTally();
//            $proposalTally->setPollProposalId($proposal->getUuid());
//            $proposalTally->setGradesUuids($poll->getGradesUuids());
//            $proposalTally->setGradesTally($gradesTally);
//            // Setting these later once we have all the tallies
//            //$proposalTally->setGrade(?);
//            //$proposalTally->setRank(?);
//
//            $proposalsTallies[] = $proposalTally;
        }

        return $tallyPerProposal;
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
