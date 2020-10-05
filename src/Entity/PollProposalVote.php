<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Vote on a Proposal of a Majority Judgment Poll.
 *
 * TBD:
 * A Vote is immutable.
 * A Vote cannot be deleted.
 *
 *
 * @ApiResource(
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"PollProposalVote:read"}},
 *         },
 *         "delete"={
 *             "access_control"="is_granted('can_delete', object)",
 *         },
 *     },
 *     collectionOperations = {
 *         "post"={
 *             "denormalization_context"={"groups"={"PollProposalVote:create"}},
 *             "normalization_context"={"groups"={"PollProposalVote:read"}},
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass="\App\Repository\PollProposalVoteRepository")
 */
class PollProposalVote
{
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
     * @Groups({"ProllProposalVote:read"})
     */
    public $uuid;

    /**
     * The Majority Judgment Poll Proposal the author is giving a mention to.
     *
     * @Groups({"ProllProposalVote:create", "ProllProposalVote:read"})
     * @ORM\ManyToOne(targetEntity="PollProposal", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $proposal;

    /**
     * The name of the author of the vote, if any was specified.
     *
     * @Groups({"ProllProposalVote:create", "ProllProposalVote:read"})
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $author_name;

    /**
     * The mention attributed by the author to the proposal.
     *
     * @Groups({"ProllProposalVote:create", "ProllProposalVote:read"})
     * @ORM\Column(type="string", length=16)
     */
    private $mention;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="votes")
     */
    private $elector; // $judge / $author / $voter

    public function __construct()
    {
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

    public function getProposal(): ?PollProposal
    {
        return $this->proposal;
    }

    public function setProposal(?PollProposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }
    
    public function getMention(): ?string
    {
        return $this->mention;
    }

    public function setMention(string $mention): self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->author_name;
    }

    public function setAuthorName(?string $author_name): self
    {
        $this->author_name = $author_name;

        return $this;
    }

    public function getElector(): ?User
    {
        return $this->elector;
    }

    public function setElector(?User $elector): self
    {
        $this->elector = $elector;

        return $this;
    }

}
