<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;


/**
 * Amount of Judgments for one Grade and Proposal.
 * This is where the main input of this library is set.
 * We don't care how you collect the Judgments.
 * Only the tally (= amount of Judgments) for each Grade and Proposal is required.
 *
 * You may implement this interface in your own classes,
 * or use our implementation of it, `ProposalGradeTally`.
 *
 * Interface ProposalGradeTallyInterface
 * @package MieuxVoter\MajorityJudgment\Tally
 */
interface GradeTallyInterface
{
    /**
     * The Grade this tally is about. (for one given Proposal)
     *
     * The Grade can be anything, so long as it supports the equality operator.
     * This Grade MUST be present in the Grades of the grandparent `PollTally`,
     * or an `UnknownGradeException` will be raised.
     *
     * @return mixed
     */
    public function getGrade();

    /**
     * The Proposal this tally is about. (for one given Grade)
     *
     * The Proposal may be anything, so long as it supports the equality operator.
     * This Proposal MUST be the same as the proposals of the parent `ProposalTally`,
     * or an `ProposalMismatchException` will be raised.
     *
     * @return mixed
     */
    public function getProposal();

    /**
     * The amount of (explicit) Judgments of this Grade, received by this Proposal.
     *
     * @return int
     */
    public function getTally() : int;
}
