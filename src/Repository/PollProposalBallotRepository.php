<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Proposal\Ballot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;


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
     * The current implementation (one SQL query per proposal, left join) may not scale overly well
     * with the amount of ballots|judgments.   This needs stress testing!
     * An alternative would be to archive past judgments in another table,
     * in order to remove the expensive left join here.
     *
     * @param Poll $poll
     * @return array Associative array of proposal's UUID as string => [1, 4, 2, …]
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function getTallyPerProposal(Poll $poll) : array
    {
        $tallyPerProposal = array();
        $grades = $poll->getGradesInOrder();

        foreach ($poll->getProposals() as $proposal) {

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
            foreach ($grades as $k => $grade) {
                $qb->addSelect("SUM(CASE WHEN j.grade = :g$k THEN 1 ELSE 0 END) grade$k");
                $qb->setParameter("g$k", $grade);
            }

//            dump($qb->getQuery()->getSQL());
            // SELECT COUNT(b0_.id) AS sclr_0, SUM(CASE WHEN b0_.grade_id = ? THEN 1 ELSE 0 END) AS sclr_1, SUM(CASE WHEN b0_.grade_id = ? THEN 1 ELSE 0 END) AS sclr_2, SUM(CASE WHEN b0_.grade_id = ? THEN 1 ELSE 0 END) AS sclr_3 FROM ballot b0_ LEFT JOIN ballot b1_ ON (b0_.proposal_id = b1_.proposal_id AND b0_.participant_id = b1_.participant_id AND b0_.id < b1_.id) WHERE b0_.proposal_id = ? AND b1_.proposal_id IS NULL ORDER BY b0_.id ASC
            // ~430 chars, ~60 chars per grade, 3 grades here
            // How low is the SQL limit ?  It's not 1024, I've done bigger than this.

            $proposalTallyRow = $qb
                    ->getQuery()
                    ->getOneOrNullResult();

            if (null === $proposalTallyRow) {
                throw new Exception("getTallyPerProposal() failed");
            }

            $proposalTally = array();
            foreach ($grades as $k => $grade) {
                $proposalTally[] = (int) $proposalTallyRow['grade'.$k];
            }

            $tallyPerProposal[$proposal->getUuid()->toString()] = $proposalTally;
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
