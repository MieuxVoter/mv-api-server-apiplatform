<?php


namespace App\Entity\Poll;


use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\GetResultController;
use App\Entity\Poll;
//use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Poll\Proposal\Result as ProposalResult;


/**
 * A Result of a Majority Judgment Poll.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     shortName="Result",
 *     itemOperations={
 *         "get_for_poll"={
 *             "method"="GET",
 *             "controller"=GetResultController::class,
 *             "path"="/polls/{id}/result.{_format}",
 *             "read"=false,
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
class Result
{
    /**
     * This a stub to fool ApiPlatform.  See Issue #17.
     * We don't need an identifier, as this entity is not in the database.
     *
     * @var string
     * @ApiProperty(identifier=true)
     */
    private $id = "identifier_stub_see_issue_17";

    /**
     * The Poll this Result describes.
     *
     * @var Poll
     * @Groups({"read"})
     */
    private $poll;

    /**
     * The name of the algorithm used to derive this Result.
     * Default: "standard".
     *
     * @var string
     * @Groups({"read"})
     */
    private $algorithm;

    // algorithm parameters?  perhaps use an entity as $algorithm?

    /**
     * In order, each Proposals' Result.  In extreme,
     * low-participation polls, some Proposals may have the same rank ;
     * in that case, their order should be the order they were added to the poll.
     *
     * @var ProposalResult[]
     * @Groups({"read"})
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "type"="array",
     *             "items"={
     *                 "$ref"="#/components/schemas/ProposalResultRead",
     *             },
     *         },
     *     },
     * )
     */
    private $leaderboard;  // $proposals?  $proposalsResults?

    ///
    ///

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