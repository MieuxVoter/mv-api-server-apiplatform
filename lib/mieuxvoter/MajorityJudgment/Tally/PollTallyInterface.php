<?php


namespace MieuxVoter\MajorityJudgment\Tally;


/**
 * A Tally holds the amount of Judgments for each Grade, on each Proposal.
 * It also needs to hold the total amount of Participants, in order to account for default grades.
 *
 * You may implement this interface in your own classes,
 * or use our implementation of it, `PollTally`.
 *
 * Interface PollTallyInterface
 * @package MieuxVoter\MajorityJudgment\Tally
 */
interface PollTallyInterface
{
    /**
     * Total amount of Participants in the Poll.
     * Participants are not required to give a Grade to each Proposal,
     * so this information helps accounting for default Grades.
     *
     * @return int
     */
    public function getParticipantsAmount() : int;

    /**
     * @return ProposalTallyInterface[]
     */
    public function getProposalsTallies() : iterable;
}
