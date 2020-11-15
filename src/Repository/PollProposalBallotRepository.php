<?php

namespace App\Repository;

use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Proposal\Ballot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
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
            'proposal' => array_map(function(Proposal $proposal){
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
