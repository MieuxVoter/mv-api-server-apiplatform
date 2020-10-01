<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Candidate of a Liquid Majority Judgment Poll whom any Elector can give a Mention to.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"read"}},
 *         },
 *     },
 *     collectionOperations={
 *         "post"={
 *             "denormalization_context"={"groups"={"create"}},
 *         },
 *     }
 * )
 * @ORM\Entity(
 *     repositoryClass="App\Repository\LimajuPollCandidateRepository",
 * )
 */
class PollCandidate
{
    /**
     * @var UuidInterface
     *
     * @Groups({ "read" })
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @Groups({ "create", "read" })
     * @ORM\Column(type="string", length=142)
     */
    private $title;

    /**
     * The poll this candidate is attached to.
     *
     * @Groups({ "create" })
     * @ORM\ManyToOne(targetEntity="Poll", inversedBy="candidates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

//    /**
//     * @ORM\OneToMany(targetEntity="App\Entity\LimajuCandidateVote", mappedBy="candidate", orphanRemoval=true)
//     */
//    private $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    public function getId()
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

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * @return Collection|LimajuPollCandidateVote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(LimajuPollCandidateVote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setCandidate($this);
        }

        return $this;
    }

    public function removeVote(LimajuPollCandidateVote $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getCandidate() === $this) {
                $vote->setCandidate(null);
            }
        }

        return $this;
    }
}
