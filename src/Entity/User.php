<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MsgPhp\Domain\Event\DomainEventHandler;
use MsgPhp\Domain\Event\DomainEventHandlerTrait;
use MsgPhp\User\Credential\EmailPassword;
use MsgPhp\User\Model\EmailPasswordCredential;
use MsgPhp\User\Model\ResettablePassword;
use MsgPhp\User\Model\RolesField;
use MsgPhp\User\User as BaseUser;
use MsgPhp\User\UserId;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * We ditched FosUserBundle for the new MsgPhp\User
 *
 * @ORM\Entity()
 * @UniqueEntity(fields={"name"}, message="There is already an account with this name")
 */
class User extends BaseUser implements DomainEventHandler, UserInterface
{
    use DomainEventHandlerTrait;
    use EmailPasswordCredential;
    use ResettablePassword;
    use RolesField;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="msgphp_user_id", length=191)
     */
    private $id;

    /**
     * @Groups({ "create", "read", "update" })
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    public $name;

    /**
     * Authored Limaju Polls.
     *
     * @Groups({ "never" })
     * @ORM\OneToMany(targetEntity="Poll", mappedBy="author")
     */
    private $limajuPolls;


    public function __construct(UserId $id, string $email, string $password)
    {
        $this->id = $id;
//        $this->name = $name;
        $this->credential = new EmailPassword($email, $password);
        $this->limajuPolls = new ArrayCollection();
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->getCredential()->getUsername();
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials() {}

    /**
     * @return Collection|Poll[]
     */
    public function getLimajuPolls(): Collection
    {
        return $this->limajuPolls;
    }

    public function addLimajuPoll(Poll $limajuPoll): self
    {
        if (!$this->limajuPolls->contains($limajuPoll)) {
            $this->limajuPolls[] = $limajuPoll;
            $limajuPoll->setAuthor($this);
        }

        return $this;
    }

    public function removeLimajuPoll(Poll $limajuPoll): self
    {
        if ($this->limajuPolls->contains($limajuPoll)) {
            $this->limajuPolls->removeElement($limajuPoll);
            // set the owning side to null (unless already changed)
            if ($limajuPoll->getAuthor() === $this) {
                $limajuPoll->setAuthor(null);
            }
        }

        return $this;
    }
}
