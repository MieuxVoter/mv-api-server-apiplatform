<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;


/**
 * The Tally for a Proposal, that is the tallies for each Grade.
 *
 * Interface ProposalTallyInterface
 * @package MieuxVoter\MajorityJudgment\Tally
 */
interface ProposalTallyInterface
{
    /**
     * @return mixed
     */
    public function getProposal();

    /**
     * @return GradeTallyInterface[]
     */
    public function getGradesTallies() : iterable;
}