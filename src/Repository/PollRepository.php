<?php


namespace App\Repository;


use App\Entity\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;


/**
 * @method Poll|null find($id, $lockMode = null, $lockVersion = null)
 * @method Poll|null findOneBy(array $criteria, array $orderBy = null)
 * @method Poll|null findOneByUuid($pollId)
 * @method Poll|null findOneBySlug($pollSlug)
 * @method Poll[]    findAll()
 * @method Poll[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Poll::class);
    }


    /**
     * Finds a poll from an identifier or identifier-like string.
     * Sugar method.
     *
     * @param string $idLike
     * @return Poll|null
     */
    public function findOneByIdLike(string $idLike)
    {
        if (8 > count_chars($idLike)) {
            return null;
        }

        $pollWithUuid = $this->findOneByUuid($idLike);
        if ($pollWithUuid) {
            return $pollWithUuid;
        }

        $poll = $this->findOneWithUuidStartingWith($idLike);
        if ($poll) {
            return $poll;
        }

        $poll = $this->findOneWithUuidStartingWith(str_replace('-', '', $idLike));
        if ($poll) {
            return $poll;
        }

        $poll = $this->findOneBySlug(strtolower($idLike));
        if ($poll) {
            return $poll;
        }

        return null;
    }


    /**
     * Finds a poll from a partial uuid.
     *
     * @param $uuidPrefix
     * @return Poll|null
     */
    protected function findOneWithUuidStartingWith(string $uuidPrefix)
    {
        $polls = $this->createQueryBuilder('p')
            ->andWhere('p.uuid LIKE :id')
            ->setParameter('id', addcslashes($uuidPrefix, "%_").'%')
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if ( ! empty($polls)) {
            return $polls[0];
        }

        return null;
    }


    public function countParticipants(Poll $poll) : int
    {
        $qbu = $this->_em->createQueryBuilder();
        $qbu->select('COUNT(DISTINCT b.participant) as participants_amount')
            ->from(Poll\Proposal\Ballot::class, 'b')
            ->where('b.proposal IN (:proposals)')
            ->setParameter('proposals', $poll->getProposals());
        $amount = $qbu->getQuery()->getSingleResult();

//        if (empty($amount)) {
//            throw new \Exception("What? No!");
//        }

        return (int) $amount['participants_amount'];
    }

}
