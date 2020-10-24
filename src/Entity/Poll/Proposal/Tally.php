<?php


namespace App\Entity\Poll\Proposal;


use App\Entity\Poll\Grade;
use App\Entity\Poll\Proposal;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * The Tally of one Proposal.
 *
 * Class Tally
 * @package App\Entity\Poll\Proposal
 */
class Tally
{
    /**
     * @var Proposal
     * @Groups({"read"})
     */
    private $proposal;

    /**
     * Rank starts at 1 and goes upwards.
     * Two proposals may have the same rank.
     *
     * @var int Rank of the proposal in the poll.
     * @Groups({"read"})
     */
    private $rank;

    /**
     * @var Grade
     * @Groups({"read"})
     */
    private $median_grade;

    ///

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