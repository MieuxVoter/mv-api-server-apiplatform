<?php


namespace App\Entity\Poll;


use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Poll;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\GetOrCreateInvitationsController;
use App\Controller\AcceptInvitationController;


/**
 * An invitation to a poll.
 * Those are created on-demand.
 *
 * Only polls with scope Poll::SCOPE_PRIVATE require invitations.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"Invitation:read"}},
 *     itemOperations={
 *         "get"={
 *             "method"="GET",
 *             "controller"=AcceptInvitationController::class,
 *         },
 *     },
 *     collectionOperations={
 *         "get",
 *         "get_for_poll"={
 *             "method"="GET",
 *             "controller"=GetOrCreateInvitationsController::class,
 *             "path"="/polls/{pollId}/invitations.{_format}",
 *             "access_control"="is_granted('can_create_invitations', object)",
 *         },
 *     },
 * )
 * @ORM\Entity(
 *     repositoryClass="App\Repository\PollInvitationRepository",
 * )
 */
class Invitation
{
    /**
     * @var int|null
     * @ApiProperty(identifier=false)
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
     * @Groups({"Invitation:read"})
     */
    private $uuid;

    /**
     * The poll this invitation is for.
     * Only polls with scope Poll::SCOPE_PRIVATE require invitations.
     *
     * @Groups({"Invitation:create", "Invitation:read"})
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Poll",
     *     inversedBy="invitations"
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

    /**
     * As long as this is empty, the invitation is still open.
     * Should we make a Participant Entity?
     *
     * @var User|null
     * @Groups({"Invitation:read"})
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     inversedBy="invitations"
     * )
     * @ORM\JoinColumn(nullable=true)
     */
    private $participant;

    /**
     * The user that authored this invitation.
     * Probably should not be null, we'll see…
     *
     * Useful in cases where participants are able to author invitations as well.
     * - Up to a total fixed by organizers
     * - Exponential decrease?
     * - API to burn the invitations I authored that were not accepted
     *
     * @var User|null
     * Groups({"Invitation:read"})
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User",
     *     inversedBy="authored_invitations"
     * )
     * @ORM\JoinColumn(nullable=true)
     */
    private $author;

    //
    //

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    /**
     * Whether this invitation was accepted by someone already.
     *
     * @Groups({"Invitation:read"})
     * @return bool
     */
    public function isAccepted()
    {
        return null !== $this->participant;
    }

    /**
     * @return UuidInterface|null
     */
    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return Poll|null
     */
    public function getPoll() : ?Poll
    {
        return $this->poll;
    }

    /**
     * @param mixed $poll
     */
    public function setPoll(Poll $poll): void
    {
        $this->poll = $poll;
    }

    /**
     * @return User|null
     */
    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    /**
     * @param User|null $participant
     */
    public function setParticipant(?User $participant): void
    {
        $this->participant = $participant;
    }

    /**
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * @param User|null $author
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

}
