<?php


namespace App\Entity\Poll;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Poll;
use App\Entity\Poll\Proposal\Result as ProposalResult;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\GetResultController;


/**
 * @ApiResource(
 *     itemOperations={
 *         "get_for_poll"={
 *             "method"="GET",
 *             "controller"=GetResultController::class,
 *             "path"="/polls/{id}/results.{_format}",
 *             "read"=false,
 *         },
 *     },
 *     collectionOperations={},
 * )
 *
 * Class Result
 * @package App\Entity\Poll
 */
class Result
{
    /**
     * @var string
     * @ApiProperty(identifier=true)
     * @Groups({"read"})
     */
    private $id;

    /**
     * The poll this result is of.
     *
     * @var Poll
     * @Groups({"read"})
     */
    private $poll;

    /**
     * Default: "standard"
     *
     * @var string The algorithm used to compute this poll result..
     * @Groups({"read"})
     */
    private $algorithm;

    /**
     * @var []ProposalResult
     * @Groups({"read"})
     */
    private $leaderboard;

    ///
    ///

    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
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
}