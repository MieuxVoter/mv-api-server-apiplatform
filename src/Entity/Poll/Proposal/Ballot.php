<?php


namespace App\Entity\Poll\Proposal;


use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateBallotController;
use App\Entity\Poll\Grade;
use App\Entity\Poll\Proposal;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * A Ballot holds a Judgment on a Proposal, by a Participant of a Poll.
 *
 * The sacred text is titled "Judge! Don't Vote."  ;)
 *
 * Rules TBD:
 *
 * RULESET A: Immutability
 * A Ballot is immutable.
 * A Ballot cannot be deleted.
 * Multiple Ballots may be recorded, and only the most recent one should matter in the Result.
 *
 * RULESET B: Mutability
 * A Ballot is mutable.
 * Only one Ballot per Proposal and Participant.
 *
 * Right now we implement neither A nor B.  There are no Gherkin features for this yet.  Help us decide!
 *
 * @ApiResource(
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
 *             "path"="/polls/{pollId}/proposals/{proposalId}/ballots.{_format}",
 *             "method"="POST",
 *             "controller"=CreateBallotController::class,
 *             "denormalization_context"={"groups"={"create"}},
 *             "normalization_context"={"groups"={"created"}},
 *             "openapi_context"={
 *                 "parameters"={
 *                     {
 *                         "name": "pollId",
 *                         "in": "path",
 *                         "required": true,
 *                         "schema"={
 *                             "type"="string",
 *                         },
 *                     },
 *                     {
 *                         "name": "proposalId",
 *                         "in": "path",
 *                         "required": true,
 *                         "schema"={
 *                             "type"="string",
 *                         },
 *                     },
 *                 },
 *             },
 *         },
 *     },
 * )
 *             "access_control"="is_granted('ROLE_USER')",
 * @ORM\Entity(
 *     repositoryClass="App\Repository\PollProposalBallotRepository",
 * )
 */
class Ballot
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
     * @Groups({"read", "created"})
     */
    public $uuid;

    /**
     * The Majority Judgment Poll Proposal the author is giving a grade to.
     *
     * @Groups({"read", "created"})
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Poll\Proposal",
     *     inversedBy="ballots",
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $proposal;

    /**
     * The name of the author of the vote, if any was specified.
     * TBD: May be deprecated soon, and is probably never set.
     *
     * Groups({"create", "read"})
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $author_name;

    /**
     * The Grade attributed by the Judge to the Proposal.
     *
     * @var Grade
     * @Groups({"create", "read", "created"})
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Poll\Grade",
     * )
     */
    private $grade;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     inversedBy="ballots"
     * )
     */
    private $participant;

    /**
     * @var \DateTime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    ///
    ///

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

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }
    
    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(Grade $grade): self
    {
        $this->grade = $grade;

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

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

}
