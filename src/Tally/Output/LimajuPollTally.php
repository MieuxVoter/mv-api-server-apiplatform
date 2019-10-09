<?php


namespace App\Tally\Output;


/**
 * A count of the votes.
 * The value are floats instead of integers because we may have complex tallybots with transitivity friction|entropy.
 * The purpose of a TallyBot is to generate and output an instance of this.
 */
class LimajuPollTally
{
    /**
     * @var array|LimajuPollCandidateTally[]
     */
    public $candidates;

    /**
     * Tally constructor.
     * @param array $candidates
     */
    public function __construct(array $candidates)
    {
        $this->candidates = $candidates;
    }

    public function countVotes()
    {
        $count = 0;
        foreach ($this->candidates as $candidate) {
            $count += $candidate->countVotes();
        }

        return $count;
    }
}