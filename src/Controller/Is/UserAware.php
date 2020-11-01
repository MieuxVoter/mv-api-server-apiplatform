<?php


namespace App\Controller\Is;


use App\Entity\User;
use Symfony\Component\Security\Core\Security;


trait UserAware {
    /**
     * @var Security
     */
    protected $security;

    /**
     * @required
     * @param Security $security
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /**
     * @return Security
     */
    public function getSecurity(): Security
    {
        return $this->security;
    }

    /**
     * ???
     *
     * @return User
     */
    public function getUser(): User
    {
        /** @var User $user */
        $user = $this->getSecurity()->getUser();

        return $user;
    }
}