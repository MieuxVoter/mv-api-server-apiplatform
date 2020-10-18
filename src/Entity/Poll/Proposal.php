<?php


namespace App\Entity\Poll;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Poll;
use App\Entity\Poll\Proposal\Ballot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

// *             "path"="/polls/{pollId}/proposals/{proposalId}",

/**
 * A Proposal of a Poll whom any Judge can give a Grade (aka. Mention) to.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"Proposal:read"}},
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"Proposal:read"}},
 *         },
 *     },
 *     collectionOperations={
 *         "post"={
 *             "method"="POST",
 *             "denormalization_context"={"groups"={"Proposal:create"}},
 *             "path"="/polls/{pollId}/proposals",
 *         },
 *     },
 *     subresourceOperations={
 *         "api_polls_proposals_get_subresource"={
 *             "method"="GET",
 *             "normalization_context"={"groups"={"Proposal:read"}},
 *             "path"="/polls/{pollId}/proposals",
 *         },
 *     },
 *
 * )
 * @ORM\Entity(
 *     repositoryClass="App\Repository\PollProposalRepository",
 * )
 */
class Proposal
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
     * Universally Unique IDentifier, something like this: 10e3c5e8-4a7d-4d23-a20a-8c175bf45a92
     *
     * @var UuidInterface|null
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"Proposal:read"})
     */
    public $uuid;

    /**
     * → name ?
     *
     * @Groups({"Proposal:create", "Proposal:read", "Poll:create"})
     * @ORM\Column(type="string", length=142)
     */
    private $title;

    /**
     * The poll this proposal is attached to.
     *
     * @Groups({"Proposal:create"})
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Poll",
     *     inversedBy="proposals",
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

   /**
    * @ORM\OneToMany(
    *     targetEntity="App\Entity\Poll\Proposal\Ballot",
    *     mappedBy="proposal",
    *     orphanRemoval=true,
    * )
    * @ApiSubresource()
    */
   private $ballots;

    public function __construct()
    {
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
            $vote->setProposal($this);
        }

        return $this;
    }

    public function removeBallot(Ballot $vote): self
    {
        if ($this->ballots->contains($vote)) {
            $this->ballots->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getProposal() === $this) {
                $vote->setProposal(null);
            }
        }

        return $this;
    }
}
