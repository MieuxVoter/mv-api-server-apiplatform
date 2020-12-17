<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;


/**
 * A Tally holds the amount of Judgments for each Grade, on each Proposal.
 * It also needs to hold the total amount of Participants, in order to account for default grades.
 *
 * This is the interface of the main input of this library,
 * that which is given to the MajorityJudgmentResolver in order to derive a Result (a ranking of proposals).
 *
 * You may implement this interface in your own classes,
 * or use one of our convenience implementations of it, such as `ArrayPollTally`.
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
     * Tallies for each Proposal.
     * The order matters only in *perfect* equality scenarios.
     * In these extreme cases, the order of ex-æquo proposals in the Result
     * will reflect the order of proposals submitted here.
     *
     * @return ProposalTallyInterface[]
     */
    public function getProposalsTallies() : iterable;
}
