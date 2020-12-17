<?php


namespace MieuxVoter\MajorityJudgment\Model\Tally;


/**
 * Convenience class for ProposalTallyInterface.
 *
 * Provide it with an array of tallies, one for each grade.
 * The order goes from "worst" grade tally to "best" grade tally.
 * You may provide an associative array, and use grades as keys.
 * If you provide an indexed array, grades will be the integer keys.
 *
 * Class ArrayProposalTally
 * @package MieuxVoter\MajorityJudgment\Tally
 */
class ArrayProposalTally implements ProposalTallyInterface
{

    protected $proposal;

    protected $grades_tallies = [];

    /**
     * ArrayProposalTally constructor.
     *
     * @param $proposal
     * @param $grades_tallies_array
     */
    public function __construct($proposal, $grades_tallies_array)
    {
        $this->proposal = $proposal;

        foreach ($grades_tallies_array as $grade => $grade_tally_value) {
            assert(is_int($grade_tally_value), "A tally must be an integer.");
            assert(0 <= $grade_tally_value, "A tally must be positive.");

            $grade_tally = new GradeTally($grade, $proposal, $grade_tally_value);
            $this->grades_tallies[] = $grade_tally;
        }
    }

    /**
     * @return mixed
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * @return GradeTallyInterface[]
     */
    public function getGradesTallies(): iterable
    {
        return $this->grades_tallies;
    }
}