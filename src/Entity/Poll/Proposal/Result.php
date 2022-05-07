<?php

declare(strict_types=1);

namespace App\Entity\Poll\Proposal;


use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Poll\Grade;
use App\Entity\Poll\Proposal;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Poll\Grade\Result as ProposalGradeResult;


/**
 * The ranked Result of one Proposal in a Poll.
 *
 * @ApiResource(
 *     shortName="ProposalResult",
 *     normalizationContext={"groups"={"read"}},
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
    /**
     * This a stub to fool ApiPlatform.  See Issue #17.
     * We don't need an identifier, as this entity is not in the database.
     *
     * @var string
     * @ApiProperty(identifier=true)
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
     * The computed rank of the Proposal in the Poll —
     * Rank starts at 1 and goes upwards, and
     * two proposals may have the same rank.
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
    private $medianGrade; // camelCase required by ApiPlatform

    /**
     * Total Amount of Ballots emitted for the Proposal this Result is about.
     * This includes the "ghost", default ballots.
     *
     * @var int
     * @Groups({"read"})
     */
    private $tally;

    /**
     * Results for each Grade, on this Proposal —
     * This is the merit profile of the Proposal.
     *
     * @ApiProperty(
     *     attributes={
     *         "openapi_context"={
     *             "type"="array",
     *             "items"={
     *                 "$ref"="#/components/schemas/ProposalGradeResultRead",
     *             },
     *         },
     *     },
     * )
     * @var ProposalGradeResult[]
     * @Groups({"read"})
     */
    private $gradesResults; // $meritProfile?

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
        return $this->medianGrade;
    }

    /**
     * @param Grade $medianGrade
     */
    public function setMedianGrade(Grade $medianGrade): void
    {
        $this->medianGrade = $medianGrade;
    }

    /**
     * @return int
     */
    public function getTally(): int
    {
        return $this->tally;
    }

    /**
     * @param int $tally
     */
    public function setTally(int $tally): void
    {
        $this->tally = $tally;
    }

    /**
     * @return array|null
     */
    public function getGradesResults() : ?array
    {
        return $this->gradesResults;
    }

    /**
     * @param mixed $gradesResults
     */
    public function setGradesResults($gradesResults): void
    {
        $this->gradesResults = $gradesResults;
    }

    public function addGradeResult(ProposalGradeResult $gradeResult)
    {
        if (null === $this->gradesResults) {
            $this->gradesResults = array();
        }
        $this->gradesResults[] = $gradeResult;
    }
}