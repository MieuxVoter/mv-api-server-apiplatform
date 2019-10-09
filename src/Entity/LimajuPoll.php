<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Liquid Majority Judgment Poll.
 * A poll is not editable after creation.
 * A poll cannot be deleted without privileges.
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
 *         "post"={
 *             "denormalization_context"={"groups"={"create"}},
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\LimajuPollRepository")
 */
class LimajuPoll
{

    const MENTION_EXCELLENT  = 'bonega';
    const MENTION_VERY_GOOD  = 'trebona';
    const MENTION_GOOD       = 'bona';
    const MENTION_PASSABLE   = 'trairebla';
    const MENTION_INADEQUATE = 'neadekvata';
    const MENTION_MEDIOCRE   = 'malboneta';
    const MENTION_TO_REJECT  = 'malakcepti';


    /**
     * @var UuidInterface
     *
     * @Groups({ "read" })
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({ "create", "read", "update" })
     * @ORM\Column(type="string", length=142)
     */
    private $title;

    /**
     * @Groups({ "create", "read", "update" })
     * @ApiSubresource()
     * @ORM\OneToMany(
     *     targetEntity="LimajuPollCandidate",
     *     mappedBy="poll",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     */
    private $candidates;

    /**
     * @Groups({ "none" })
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="limajuPolls")
     */
    private $author;


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function __construct()
    {
        $this->candidates = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }



    /**
     * @return Collection|LimajuPollCandidate[]
     */
    public function getCandidates(): Collection
    {
        return $this->candidates;
    }

    public function addCandidate(LimajuPollCandidate $candidate): self
    {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates[] = $candidate;
            $candidate->setPoll($this);
        }

        return $this;
    }

    public function removeCandidate(LimajuPollCandidate $candidate): self
    {
        if ($this->candidates->contains($candidate)) {
            $this->candidates->removeElement($candidate);
            // set the owning side to null (unless already changed)
            if ($candidate->getPoll() === $this) {
                $candidate->setPoll(null);
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
