<?php


namespace MieuxVoter\MajorityJudgment\Model\Result;


/**
 * An element of the leaderboard of a PollResult.
 *
 * Class ProposalResult
 * @package MieuxVoter\MajorityJudgment\Result
 */
class ProposalResult
{

    /**
     * One of the proposals submitted in the PollTally.
     * It may have any type, for convenience.
     *
     * @var mixed $proposal
     */
    protected $proposal;


    /**
     * Rank of the Proposal, in the Result.
     *
     * Two proposals may share the same rank.
     * The "best" proposal will have rank 1.
     * The rank increases continuously.
     *
     * @var int $rank
     */
    protected $rank;


    /**
     * The higher the score, the better this Proposal is considered.
     * It depends on the meaning of the grades, of course.
     * Higher scores means higher grades; and vice-versa.
     * Scores are strings, compared lexicographically.
     *
     * @var string $score
     */
    protected $score;


    /**
     * Median Grade.
     *
     * @var mixed $median
     */
    protected $median;


    /**
     * @return mixed
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * @param mixed $proposal
     */
    public function setProposal($proposal): void
    {
        $this->proposal = $proposal;
    }

    /**
     * @return string
     */
    public function getScore(): string
    {
        return $this->score;
    }

    /**
     * @param string $score
     */
    public function setScore(string $score): void
    {
        $this->score = $score;
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
     * @return mixed
     */
    public function getMedian()
    {
        return $this->median;
    }

    /**
     * @param mixed $median
     */
    public function setMedian($median): void
    {
        $this->median = $median;
    }

}