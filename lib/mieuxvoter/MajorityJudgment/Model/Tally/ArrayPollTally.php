<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;



/**
 * Create a Tally from a PHP associative array (and the amount of participants).
 *
    * $tally = new ArrayPollTally(21, [
      * 'Proposal A' => [1, 1, 4, 3, 7, 4, 1],
      * 'Proposal B' => [0, 2, 4, 6, 4, 2, 3],
    * ]);
 *
 * For each Proposal, the tallies of the grades are given in order,
 * from "worst" grade to "best" grade.
 * The order of the proposals matters, but only in *perfect* equality cases,
 * if the Resolver is not configured to randomize.
 *
 * Class ArrayPollTally
 * @package MieuxVoter\MajorityJudgment\Tally
 */
class ArrayPollTally implements PollTallyInterface
{

    protected $participants_amount;

    protected $proposals_tallies = [];


    /**
     * BasicPollTally constructor.
     * @param int $participants_amount
     * @param iterable $tally_per_proposal
     */
    public function __construct(int $participants_amount, iterable $tally_per_proposal)
    {
        $this->participants_amount = $participants_amount;

        foreach ($tally_per_proposal as $proposal => $proposal_tally_array) {
            $proposal_tally = new ArrayProposalTally($proposal, $proposal_tally_array);
            $this->proposals_tallies[] = $proposal_tally;
        }
    }


    /**
     * Total amount of Participants in the Poll.
     * Participants are not required to give a Grade to each Proposal,
     * so this information helps accounting for default Grades.
     *
     * @return int
     */
    public function getParticipantsAmount(): int
    {
        return $this->participants_amount;
    }


    /**
     * @return ProposalTallyInterface[]
     */
    public function getProposalsTallies(): iterable
    {
        return $this->proposals_tallies;
    }
}
