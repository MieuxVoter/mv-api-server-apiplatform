<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, groups={"register", "edit"}, message="Email already in use")
 * @ApiResource(
 *     attributes={
 *         "normalization_context"={"groups"={"User:read"}},
 *         "denormalization_context"={"groups"={"User:edit"}},
 *         "validation_groups"={"register", "edit"}
 *     },
 *     collectionOperations={
 *         "get"={
 *              "method"="GET",
 *              "access_control"="is_granted('ROLE_ADMIN')"
 *          },
 *         "post"={
 *              "method"="POST",
 *              "access_control"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY') or is_granted('ROLE_ADMIN')",
 *              "denormalization_context"={"groups"={"User:create"}},
 *              "validation_groups"={"register"}
 *          },
 *     },
 *     itemOperations={
 *         "get"={
 *              "method"="GET",
 *              "access_control"="is_granted('ROLE_USER') and object == user or is_granted('ROLE_ADMIN')",
 *              "normalization_context"={"groups"={"User:read"}},
 *         },
 *         "put"={
 *              "method"="PUT",
 *              "access_control"="is_granted('ROLE_USER') and object == user or is_granted('ROLE_ADMIN')",
 *              "normalization_context"={"groups"={"User:read"}},
 *              "denormalization_context"={"groups"={"User:edit"}}
 *          },
 *         "delete"={
 *              "method"="DELETE",
 *              "access_control"="is_granted('ROLE_ADMIN')"
 *          },
 *     }
 * )
 */
class User implements UserInterface
{    
    /**
     * @var int|null
     * @ApiProperty(identifier=false)
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;    

    /**
     * @var UuidInterface|null
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"User:read"})
     */
    public $uuid;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"User:create", "User:read", "User:edit"})
     * @Assert\Email(groups={"register", "edit"})
     * @Assert\NotBlank(groups={"register", "edit"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"User:create", "User:read", "User:edit"})
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * 
     */
    private $password;

    /**
     * @Groups({"User:create", "User:edit"})
     * @Assert\NotBlank(groups={"register, login"})
     * @Assert\Length(max=1024, groups={"register", "edit", "login"})
     * @SerializedName("password")
     */
    private $plainPassword;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Poll", mappedBy="author")
     * @Groups({"User:read"})
     */
    private $polls;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PollProposalVote", mappedBy="elector")
     * @Groups({"User:read"})
     */
    private $votes;

    public function __construct()
    {
        $this->polls = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if ( ! in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection|Poll[]
     */
    public function getPolls(): Collection
    {
        return $this->polls;
    }

    public function addPoll(Poll $poll): self
    {
        if (!$this->polls->contains($poll)) {
            $this->polls[] = $poll;
            $poll->setAuthor($this);
        }

        return $this;
    }

    public function removePoll(Poll $poll): self
    {
        if ($this->polls->contains($poll)) {
            $this->polls->removeElement($poll);
            // set the owning side to null (unless already changed)
            if ($poll->getAuthor() === $this) {
                $poll->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PollProposalVote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(PollProposalVote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setElector($this);
        }

        return $this;
    }

    public function removeVote(PollProposalVote $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getElector() === $this) {
                $vote->setElector(null);
            }
        }

        return $this;
    }
}
