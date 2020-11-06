<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Poll\Grade;
use App\Entity\Poll\Proposal;
use App\Entity\Random\Slug;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A Liquid Majority Judgment Poll.
 * A poll is not editable after creation. (or perhaps after the first judgment has been cast)
 * The includes the poll's grades.
 * New proposals MAY be added during the poll, but the poll's settings should govern this.
 * A poll cannot be deleted without privileges.
 *
 * Also, how are we going to localize the error messages?
 * See https://framagit.org/limaju/limaju-server-symfony/-/issues/8
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"read"}},
 *         },
 *         "delete"={
 *             "access_control"="is_granted('can_delete', object)",
 *         },
 *     },
 *     collectionOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"read"}},
 *         },
 *         "post"={
 *             "denormalization_context"={"groups"={"create"}},
 *         },
 *     },
 * )
 * @ORM\Entity(
 *     repositoryClass="App\Repository\PollRepository",
 * )
 */
class Poll
{

    const SCOPE_PUBLIC = 'public';
    const SCOPE_UNLISTED = 'unlisted';
    const SCOPE_PRIVATE = 'private';

    /** @deprecated */
    const MENTION_EXCELLENT  = 'bonega';
    /** @deprecated */
    const MENTION_VERY_GOOD  = 'trebona';
    /** @deprecated */
    const MENTION_GOOD       = 'bona';
    /** @deprecated */
    const MENTION_PASSABLE   = 'trairebla';
    /** @deprecated */
    const MENTION_INADEQUATE = 'neadekvata';
    /** @deprecated */
    const MENTION_MEDIOCRE   = 'malboneta';
    /** @deprecated */
    const MENTION_TO_REJECT  = 'malakcepti';

    /**
     * @var int|null
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * A Universally Unique IDentifier (version 4)
     * like b7e7d328-c1fb-4af7-b258-23d08f6ed2d4
     * and you may use the short prefix to fetch a poll as well,
     * like b7e7d328 or even b7e-7d-328 to make it more human-legible.
     *
     * @var string|null  Should be a UuidInterface but ApiPlatform yells (slug support)
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="string", unique=true)
     * @Groups({"read"})
     */
    public $uuid;

    /**
     * A unique, short, human-friendly identifier for the Poll.
     *
     * @var string
     * @ORM\Column(type="string", unique=true)
     * @Groups({"read", "create", "update"})
     */
    protected $slug;

    /**
     * Creating private polls may require this to be set.
     * But public polls may well be created without any author, so this might be null.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="polls")
     */
    private $author;

    /**
     * The scope defines how the poll is accessible:
     * `public`: Everyone may access the poll, and it will be publicly listed
     * `unlisted`: Everyone may access the poll if they know its URI
     * `private`: Only invited participants may participate
     * The default scope is `unlisted`.
     *
     * @var string One of Poll::SCOPE_*
     * @Groups({"create", "read", "update"})
     * @ORM\Column(type="string", length=16)
     * @Assert\Choice(
     *     choices = {
     *         self::SCOPE_PUBLIC,
     *         self::SCOPE_UNLISTED,
     *         self::SCOPE_PRIVATE,
     *     }
     * )
     */
    private $scope = self::SCOPE_UNLISTED;

    /**
     * The subject of the poll. Careful consideration should be taken in the writing of this.
     *
     * @var string
     * @Groups({"create", "read", "update"})
     * @ORM\Column(type="string", length=142)
     * @Assert\NotBlank(
     *     message = "The poll must have a subject.",
     * )
     * @Assert\Length(
     *     min="1",
     *     max="142",
     *     minMessage = "The poll's subject must be at least {{ limit }} characters.",
     *     maxMessage = "The poll's subject can be at most {{ limit }} characters.",
     * )
     */
    private $subject;

    /**
     * A list of Proposals to judge,
     * that MUST contain at least two proposals,
     * and can have at most 256 proposals
     * but that upper limit is arbitrary
     * and may wildly vary after benchmark and discussion.
     *
     * @var ArrayCollection
     * @Groups({"create", "read", "update"})
     * @ApiSubresource()
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Poll\Proposal",
     *     mappedBy="poll",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     * The limits are arbitrary, and open (like the rest) to discussion.
     * A poll with less then two proposals make no sense.
     * We have to set a maximum limit, against abuse scenarios.  It can be higher.
     * @Assert\Count(
     *     min=2,
     *     max=256,
     *     minMessage = "You must specify at least two proposals.",
     *     maxMessage = "You cannot specify more than {{ limit }} proposals.",
     * )
     */
    private $proposals;

    /**
     * A list of Grades that Participants may give to Proposals –
     * That list MUST contain at least two Grades,
     * and at most 16 (another arbitrary limit to discuss).
     *
     * @var ArrayCollection
     * @Groups({"create", "read", "update"})
     * @ApiSubresource()
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Poll\Grade",
     *     mappedBy="poll",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     * The maximum limit of grades is arbitrary and not backed by anything.  Discuss!
     * @Assert\Count(
     *     min=2,
     *     max=16,
     *     minMessage = "You must specify at least two grades.",
     *     maxMessage = "You cannot specify more than {{ limit }} grades.",
     * )
     */
    private $grades;

    /**
     * Generated invitations for this poll.
     * This is not available to the API.
     * This property is private, has no accessors, and we don't use it.
     * It is only here for ApiPlatform and Doctrine.
     *
     * @var ArrayCollection
     * @ApiSubresource()
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Poll\Invitation",
     *     mappedBy="poll",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     */
    private $invitations;

    ///
    ///

    /**
     * Can the current user generate invitations for this poll or not?
     * Helps clients show the relevant links and forms.
     *
     * @var bool
     * @Groups({"read"})
     */
    protected $canGenerateInvitations = false;

//    protected $invitationsTotalAmount;
//    protected $invitationsPendingAmount;
//    protected $invitationsAcceptedAmount;

    ///
    ///

    public function __construct($uuid = null)
    {
        $this->proposals = new ArrayCollection();
        $this->grades = new ArrayCollection();
        if (null == $uuid) {
            $uuid = Uuid::uuid4();
        }
        $this->uuid = $uuid->toString();
        $this->slug = Slug::generate();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return Collection|Proposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
            $proposal->setPoll($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getPoll() === $this) {
                $proposal->setPoll(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Grade[]
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade): self
    {
        if ( ! $this->grades->contains($grade)) {
            $this->grades[] = $grade;
            $grade->setPoll($this);
        }

        return $this;
    }

    public function removeGrade(Grade $grade): self
    {
        if ($this->grades->contains($grade)) {
            $this->grades->removeElement($grade);
            // set the owning side to null (unless already changed)
            if ($grade->getPoll() === $this) {
                $grade->setPoll(null);
            }
        }

        return $this;
    }

    public function getGradesInOrder() : array
    {
        $grades = $this->getGrades()->toArray();
        usort($grades, function (Grade $a, Grade $b) {
            return $a->getLevel() - $b->getLevel();
        });
        return $grades;
    }

    public function getLevelsOfGrades() : array
    {
        $levels = [];
        foreach ($this->getGrades() as $grade) {
            $levels[$grade->getUuid()->toString()] = $grade->getLevel();
        }
        return $levels;
    }

    public function getGradesNames() : array
    {
        $names = [];
        foreach ($this->getGradesInOrder() as $grade) {
            $names[] = $grade->getName();
        }

        return $names;
    }

    public function getGradesUuids() : array
    {
        return array_map(function(Grade $grade) {
            return $grade->getUuid()->toString();
        }, $this->getGradesInOrder());
    }

    public function getDefaultGrade() : Grade
    {
        $grades = $this->getGradesInOrder();
        assert( ! empty($grades), "Poll should have grades.");
        return $grades[0];
    }

    public function getDefaultGradeUuid() : string
    {
        return $this->getDefaultGrade()->getUuid()->toString();
    }

    public function getDefaultGradeName() : string
    {
        return $this->getDefaultGrade()->getName();
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @return bool
     */
    public function getCanGenerateInvitations(): bool
    {
        return $this->canGenerateInvitations;
    }

    /**
     * @param bool $canGenerateInvitations
     */
    public function setCanGenerateInvitations(bool $canGenerateInvitations): void
    {
        $this->canGenerateInvitations = $canGenerateInvitations;
    }
}
