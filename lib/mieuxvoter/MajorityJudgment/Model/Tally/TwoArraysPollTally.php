<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;



/**
 * Create a Tally from two PHP indexed arrays (and the amount of participants).
 *
 * $tally = new DualArrayPollTally(21, [
 *   'ProposalA',
 *   'ProposalB',
 * ], [
 *   [1, 1, 4, 3, 7, 4, 1],
 *   [0, 2, 4, 6, 4, 2, 3],
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
class TwoArraysPollTally implements PollTallyInterface
{

    protected $participants_amount;

    protected $proposals_tallies = [];


    /**
     * TwoArraysPollTally constructor.
     * TODO: since this is a public API, best throw instead of assert
     * @param int $participants_amount
     * @param array $proposals  Must have the same shape as $tallies.
     * @param array $tallies  Must have the same shape as $proposals. Array of arrays
     */
    public function __construct(int $participants_amount, array $proposals, array $tallies)
    {
        $this->participants_amount = $participants_amount;
        assert(count($proposals) == count($tallies), "Arrays must match shape.");

        foreach ($proposals as $k => $proposal) {
            assert(isset($tallies[$k]), "Arrays must match shape.");
            $this->proposals_tallies[] = new ArrayProposalTally($proposal, $tallies[$k]);
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
