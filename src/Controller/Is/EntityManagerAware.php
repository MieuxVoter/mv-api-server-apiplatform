<?php


namespace App\Controller\Is;


use Doctrine\ORM\EntityManagerInterface;


trait EntityManagerAware {

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @return EntityManagerInterface
     */
    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setEm(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

}