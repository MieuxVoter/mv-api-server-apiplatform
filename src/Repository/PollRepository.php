<?php

namespace App\Repository;

use App\Entity\Poll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


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


    protected function findOneWithUuidStartingWith($uuidPrefix)
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

}
