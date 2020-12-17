<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;


/**
 * Instantiable implementation of a GradeTallyInterface.
 * Everything it needs is given in its constructor.
 *
 * The Proposal and the Grade may be of any type.
 *
 * Class GradeTally
 * @package MieuxVoter\MajorityJudgment\Tally
 */
class GradeTally implements GradeTallyInterface
{

    protected $grade;
    protected $proposal;
    protected $tally;

    /**
     * GradeTally constructor.
     * @param $grade
     * @param $proposal
     * @param $tally
     */
    public function __construct($grade, $proposal, $tally)
    {
        $this->grade = $grade;
        $this->proposal = $proposal;
        $this->tally = (int) $tally;
    }

    /**
     * The Grade this tally is about. (for one given Proposal)
     *
     * The Grade can be anything, so long as it supports the equality operator.
     * This Grade MUST be present in the Grades of the grandparent `PollTally`,
     * or an `UnknownGradeException` will be raised.
     *
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * The Proposal this tally is about. (for one given Grade)
     *
     * The Proposal may be anything, so long as it supports the equality operator.
     * This Proposal MUST be the same as the proposals of the parent `ProposalTally`,
     * or an `ProposalMismatchException` will be raised.
     *
     * @return mixed
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * The amount of (explicit) Judgments of this Grade, received by this Proposal.
     *
     * @return int
     */
    public function getTally(): int
    {
        return $this->tally;
    }
}