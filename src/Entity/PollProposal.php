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
 * A Proposal of a Poll whom any Judge can give a Grade (aka Mention) to.
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
     * Should only be used internally.
     *
     * @var int|null
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Universally Unique IDentifier, something looking like this: 10e3c5e8-4a7d-4d23-a20a-8c175bf45a92
     *
     * @var UuidInterface|null
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"PollProposal:read"})
     */
    public $uuid;

    /**
     * @Groups({"PollProposal:create", "PollProposal:read", "Poll:create"})
     * @ORM\Column(type="string", length=142)
     */
    private $title;

    /**
     * The poll this proposal is attached to.
     *
     * @Groups({"PollProposal:create"})
     * @ORM\ManyToOne(
     *     targetEntity="Poll",
     *     inversedBy="proposals"
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

   /**
    * @ORM\OneToMany(
    *     targetEntity="App\Entity\PollProposalVote",
    *     mappedBy="proposal",
    *     orphanRemoval=true
    * )
    * @ApiSubresource()
    */
   private $votes;

    public function __construct()
    {
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
