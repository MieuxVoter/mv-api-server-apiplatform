<?php

declare(strict_types=1);

namespace App\Entity\Poll\Grade;


use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Poll\Grade;
use App\Entity\Poll\Proposal;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Results for one Grade of one Proposal,
 * basically the tally of Ballots for this Grade and Proposal,
 * but there might be more information in there in the future.
 *
 * @ApiResource(
 *     shortName="ProposalGradeResult",
 *     normalizationContext={"groups"={"read"}},
 *     itemOperations={
 *         "get",
 *     },
 *     collectionOperations={},
 * )
 *
 * Class Result
 * @package App\Entity\Poll\Grade
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
    public $id = "identifier_stub_see_issue_17";

    /**
     * The Grade this Result is about.
     *
     * @var Grade
     * @Groups({"read"})
     * @ApiProperty(
     *     readableLink=false,
     *     writableLink=false,
     *     description="The IRI of the Grade this Result is about.",
     * )
     */
    private $grade;

    /**
     * The Proposal this Result is about.
     *
     * @var Proposal
     * @Groups({"read"})
     * @ApiProperty(
     *     readableLink=false,
     *     writableLink=false,
     *     description="The IRI of the Proposal this Result is about.",
     * )
     */
    private $proposal;

    /**
     * Amount of Ballots emitted for this Grade on the Proposal.
     *
     * @var int
     * @Groups({"read"})
     */
    private $tally;

    ///
    ///

    /**
     * @return Grade
     */
    public function getGrade(): Grade
    {
        return $this->grade;
    }

    /**
     * @param Grade $grade
     */
    public function setGrade(Grade $grade): void
    {
        $this->grade = $grade;
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


}