<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Liquid Majority Judgment Poll.
 * A poll is not editable after creation. (or perhaps after the first judgment has been cast)
 * The includes the poll's grades.
 * New proposals MAY be added during the poll, but the poll's settings should govern this.
 * A poll cannot be deleted without privileges.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"Poll:read"}},
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"Poll:read"}},
 *         },
 *         "delete"={
 *             "access_control"="is_granted('can_delete', object)",
 *         },
 *     },
 *     collectionOperations={
 *         "post"={
 *             "denormalization_context"={"groups"={"Poll:create"}},
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\PollRepository")
 */
class Poll
{

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
     * @var UuidInterface|null
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"Poll:read"})
     */
    public $uuid;

    /**
     * @Groups({"Poll:create", "Poll:read", "Poll:update"})
     * @ORM\Column(type="string", length=142)
     */
    private $subject;

    /**
     * @Groups({"Poll:create", "Poll:read", "Poll:update"})
     * @ApiSubresource()
     * @ORM\OneToMany(
     *     targetEntity="PollProposal",
     *     mappedBy="poll",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     */
    private $proposals;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="polls")
     */
    private $author;
    
    public function __construct()
    {
        $this->proposals = new ArrayCollection();
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
     * @return Collection|PollProposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(PollProposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
            $proposal->setPoll($this);
        }

        return $this;
    }

    public function removeProposal(PollProposal $proposal): self
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

    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Should we do it like this in the end?

//    /**
//     * @ORM\Column(type="string", length=32)
//     */
//    private $mention_a;
//
//    /**
//     * @ORM\Column(type="string", length=32)
//     */
//    private $mention_b;
//
//    /**
//     * @ORM\Column(type="string", length=32, nullable=true)
//     */
//    private $mention_c;
//
//    /**
//     * @ORM\Column(type="string", length=32, nullable=true)
//     */
//    private $mention_d;
//
//    /**
//     * @ORM\Column(type="string", length=32, nullable=true)
//     */
//    private $mention_e;
//
//    /**
//     * @ORM\Column(type="string", length=32, nullable=true)
//     */
//    private $mention_f;
//
//    /**
//     * @ORM\Column(type="string", length=32, nullable=true)
//     */
//    private $mention_g;

//    public function getMentionA(): ?string
//    {
//        return $this->mention_a;
//    }
//
//    public function setMentionA(string $mention_a): self
//    {
//        $this->mention_a = $mention_a;
//
//        return $this;
//    }
//
//    public function getMentionB(): ?string
//    {
//        return $this->mention_b;
//    }
//
//    public function setMentionB(string $mention_b): self
//    {
//        $this->mention_b = $mention_b;
//
//        return $this;
//    }
//
//    public function getMentionC(): ?string
//    {
//        return $this->mention_c;
//    }
//
//    public function setMentionC(?string $mention_c): self
//    {
//        $this->mention_c = $mention_c;
//
//        return $this;
//    }
//
//    public function getMentionD(): ?string
//    {
//        return $this->mention_d;
//    }
//
//    public function setMentionD(?string $mention_d): self
//    {
//        $this->mention_d = $mention_d;
//
//        return $this;
//    }
//
//    public function getMentionE(): ?string
//    {
//        return $this->mention_e;
//    }
//
//    public function setMentionE(?string $mention_e): self
//    {
//        $this->mention_e = $mention_e;
//
//        return $this;
//    }
//
//    public function getMentionF(): ?string
//    {
//        return $this->mention_f;
//    }
//
//    public function setMentionF(?string $mention_f): self
//    {
//        $this->mention_f = $mention_f;
//
//        return $this;
//    }
//
//    public function getMentionG(): ?string
//    {
//        return $this->mention_g;
//    }
//
//    public function setMentionG(?string $mention_g): self
//    {
//        $this->mention_g = $mention_g;
//
//        return $this;
//    }
}
