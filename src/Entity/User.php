<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Poll\Invitation;
use App\Entity\Poll\Proposal\Ballot;
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
use App\Controller\RegisterUserController;


/**
 * Users create, maintain and participate in Polls.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     groups={"register", "edit"},
 *     message="Email already in use."
 * )
 * @UniqueEntity(
 *     fields={"username"},
 *     groups={"register", "edit"},
 *     message="Username already in use."
 * )
 * @ApiResource(
 *     attributes={
 *         "normalization_context"={"groups"={"read"}},
 *         "denormalization_context"={"groups"={"edit"}},
 *         "validation_groups"={"register", "edit"},
 *     },
 *     collectionOperations={
 *         "get"={
 *              "method"="GET",
 *              "access_control"="is_granted('ROLE_ADMIN')",
 *              "swagger_context"=User::COLLECTION_GET_OAS_CONTEXT,
 *              "openapi_context"=User::COLLECTION_GET_OAS_CONTEXT,
 *          },
 *         "post"={
 *              "method"="POST",
 *              "controller"=RegisterUserController::class,
 *              "access_control"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY') or is_granted('ROLE_ADMIN')",
 *              "denormalization_context"={"groups"={"create"}},
 *              "validation_groups"={"register"},
 *              "swagger_context"=User::COLLECTION_POST_OAS_CONTEXT,
 *              "openapi_context"=User::COLLECTION_POST_OAS_CONTEXT,
 *          },
 *     },
 *     itemOperations={
 *         "get"={
 *              "method"="GET",
 *              "access_control"="is_granted('ROLE_USER') and object == user or is_granted('ROLE_ADMIN')",
 *              "normalization_context"={"groups"={"read"}},
 *              "swagger_context"=User::ITEM_GET_OAS_CONTEXT,
 *              "openapi_context"=User::ITEM_GET_OAS_CONTEXT,
 *         },
 *         "put"={
 *              "method"="PUT",
 *              "access_control"="is_granted('ROLE_USER') and object == user or is_granted('ROLE_ADMIN')",
 *              "normalization_context"={"groups"={"read"}},
 *              "denormalization_context"={"groups"={"edit"}},
 *              "swagger_context"=User::ITEM_PUT_OAS_CONTEXT,
 *              "openapi_context"=User::ITEM_PUT_OAS_CONTEXT,
 *          },
 *         "delete"={
 *              "method"="DELETE",
 *              "access_control"="is_granted('ROLE_ADMIN')",
 *          },
 *     }
 * )
 */
class User implements UserInterface
{
    // These constants are not translation-friendly?  Do ApiPlatform have its own domain?

    const COLLECTION_GET_OAS_CONTEXT = [
        "summary" => "Retrieves the collection of Users.",
        "description" => "Only administrators are allowed to access this.",
        "tags" => ['User', 'Administration'],
    ];
    const COLLECTION_POST_OAS_CONTEXT = [
        "summary" => "Registers a new User.",
        "description" => "Registers a new User in the database.  The email is optional and will help you reset a forgotten password.",
        "tags" => ['User', 'Registration'],
    ];
    const ITEM_GET_OAS_CONTEXT = [
        "summary" => "Gets information about a User.",
        "description" => "You are authorized to get information about yourself only.",
        "tags" => ['User'],
    ];
    const ITEM_PUT_OAS_CONTEXT = [
        "summary" => "Updates information about a User.",
        "description" => "Logged-in users are authorized to update information about themselves only.",
        "tags" => ['User'],
    ];

    ///
    ///

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
     * @Groups({"read"})
     */
    public $uuid;

    /**
     * When provided, the email must be unique amongst Users.
     *
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     * @Groups({"create", "read", "edit"})
     * @Assert\Email(groups={"register", "edit"})
     */
    private $email;

    /**
     * The username must be unique amongst Users.
     *
     * @ORM\Column(type="string", length=64, unique=true)
     * @Groups({"create", "read", "edit"})
     *
     * Assert\NotBlank(groups={"register", "edit"})
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * The hashed password, stored in the database.
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * The plain password of the User.
     * This is not stored in the database.
     *
     * @var string
     * @Groups({"create", "edit"})
     * @Assert\NotBlank(groups={"register, login"})
     * @Assert\Length(max=1024, groups={"register", "edit", "login"})
     * @SerializedName("password")
     */
    private $plainPassword;
    
    /**
     * The polls authored by this User.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Poll", mappedBy="author")
     * @Groups({"read"})
     */
    private $polls;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Poll\Proposal\Ballot", mappedBy="participant")
     *
     * TBD: do we expose the most recent ballots?  It would probably be helpful.
     * Groups({"read"})
     */
    private $ballots;

    /**
     * Invitations that were accepted by this User.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Poll\Invitation", mappedBy="participant")
     *
     * TBD: do we enable reading those?
     * Groups({"read"})
     */
    private $accepted_invitations;

    /**
     * Invitations that were authored by this User.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Poll\Invitation", mappedBy="author")
     *
     * TBD: Do we allow reading those here ?
     * → We don't NEED to, it would be for convenience
     * → Security issue
     * Groups({"read"})
     */
    private $authored_invitations;

    ///
    ///

    public function __construct()
    {
        $this->polls = new ArrayCollection();
        $this->ballots = new ArrayCollection();
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
     * @return Collection|Ballot[]
     */
    public function getBallots(): Collection
    {
        return $this->ballots;
    }

    public function addBallot(Ballot $vote): self
    {
        if (!$this->ballots->contains($vote)) {
            $this->ballots[] = $vote;
            $vote->setParticipant($this);
        }

        return $this;
    }

    public function removeBallot(Ballot $vote): self
    {
        if ($this->ballots->contains($vote)) {
            $this->ballots->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getParticipant() === $this) {
                $vote->setParticipant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getAcceptedInvitations()
    {
        return $this->accepted_invitations;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getAuthoredInvitations()
    {
        return $this->authored_invitations;
    }
}
