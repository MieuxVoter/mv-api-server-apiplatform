<?php


namespace App\Entity\Poll;


use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\GetTallyController;
use App\Entity\Poll;
//use App\Tallier\Output\PollTally as TallyOutput;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Tally of a Liquid Majority Judgment Poll.
 *
 * @ApiResource(
 *     shortName="Tally",
 *     itemOperations={
 *         "get_for_poll"={
 *             "method"="GET",
 *             "controller"=GetTallyController::class,
 *             "path"="/polls/{id}/tally.{_format}",
 *             "read"=false,
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
class Tally
{
    /**
     * UUID
     * Remove this if you can, we don't need no id.
     * @var string
     * @ApiProperty(identifier=true)
     * @Groups({"read"})
     */
    private $id;

    /**
     * The poll this tally is of.
     *
     * @var Poll
     * @Groups({"read"})
     */
    private $poll;

    /**
     * Default: "standard"
     *
     * @var string The algorithm used to compute this poll tally..
     * @Groups({"read"})
     */
    private $algorithm;

    // algorithm parameters?  perhaps use an entity as $algorithm?

    /**
     * In order, each proposals' tally.
     * Some proposals, in extreme, low-participation polls, may have the same rank.
     * In that case, their order should be the order they were defined in the poll.
     *
     * @var []ProposalTally
     * @Groups({"read"})
     */
    private $leaderboard; // $proposals?

    ///
    ///

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Poll
     */
    public function getPoll(): Poll
    {
        return $this->poll;
    }

    /**
     * @param Poll $poll
     */
    public function setPoll(Poll $poll): void
    {
        $this->poll = $poll;
    }

    /**
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * @param string $algorithm
     */
    public function setAlgorithm(string $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @return mixed
     */
    public function getLeaderboard()
    {
        return $this->leaderboard;
    }

    /**
     * @param mixed $leaderboard
     */
    public function setLeaderboard($leaderboard): void
    {
        $this->leaderboard = $leaderboard;
    }
}