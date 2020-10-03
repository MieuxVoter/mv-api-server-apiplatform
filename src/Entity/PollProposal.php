<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * An Proposal of a Poll whom any Elector can give a Mention to.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"PollProposal:read"}},
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"PollProposal:read"}},
 *         },
 *     },
 *     collectionOperations={
 *         "post"={
 *             "denormalization_context"={"groups"={"PollProposal:create"}},
 *         },
 *     }
 * )
 * @ORM\Entity(
 *     repositoryClass="App\Repository\PollProposalRepository",
 * )
 */
class PollProposal
{
    /**
     * @var UuidInterface
     *
     * @Groups({"PollProposal:read"})
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @Groups({"PollProposal:create", "PollProposal:read"})
     * @ORM\Column(type="string", length=142)
     */
    private $title;

    /**
     * The poll this proposal is attached to.
     *
     * @Groups({"PollProposal:create"})
     * @ORM\ManyToOne(targetEntity="Poll", inversedBy="proposals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

   /**
    * @ORM\OneToMany(targetEntity="App\Entity\PollProposalVote", mappedBy="proposal", orphanRemoval=true)
    */
   private $votes;

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
            $vote->setProposal($this);
        }

        return $this;
    }

    public function removeVote(PollProposalVote $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getProposal() === $this) {
                $vote->setProposal(null);
            }
        }

        return $this;
    }
}
