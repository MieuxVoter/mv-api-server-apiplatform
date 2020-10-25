<?php


namespace App\Entity\Poll\Proposal;


use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Poll\Grade;
use App\Entity\Poll\Proposal;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * The ranked Result of one Proposal in a Poll.
 *
 * @ApiResource(
 *     shortName="ProposalResult",
 *     itemOperations={
 *         "get",
 *     },
 *     collectionOperations={},
 * )
 * Class Result
 * @package App\Entity\Poll\Proposal
 */
class Result
{
    // The \n allows us to pass multiline descriptions to openapi.
    /**
     * This a stub to fool ApiPlatform.  See Issue #17.  \n
     * We don't need an identifier, as this entity is not in the database.
     *
     * @var string
     * @ApiProperty(identifier=true)
     * @Groups({"read"})
     */
    private $id = "identifier_stub_see_issue_17";

    /**
     * The Proposal this Result is for.
     *
     * @var Proposal
     * @Groups({"read"})
     */
    private $proposal;

    /**
     * The computed rank of the Proposal in the Poll.  \n
     * Rank starts at 1 and goes upwards.  \n
     * Two proposals may have the same rank.
     *
     * @var int Rank of the proposal in the poll.
     * @Groups({"read"})
     */
    private $rank;

    /**
     * The median Grade of the Proposal.
     *
     * @var Grade
     * @Groups({"read"})
     */
    private $median_grade;

    ///
    ///

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return Proposal
     */
    public function getProposal(): Proposal
    {
        return $this->proposal;
    }

    /**
     * @param Proposal $proposal
     */
    public function setProposal(Proposal $proposal): void
    {
        $this->proposal = $proposal;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return Grade
     */
    public function getMedianGrade(): Grade
    {
        return $this->median_grade;
    }

    /**
     * @param Grade $median_grade
     */
    public function setMedianGrade(Grade $median_grade): void
    {
        $this->median_grade = $median_grade;
    }
}