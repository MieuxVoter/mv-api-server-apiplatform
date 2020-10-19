<?php


namespace App\Controller\Is;


use Symfony\Component\Security\Core\Security;


trait UserAware {
    /**
     * @var Security
     */
    protected $security;

    /**
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
}