<?php


namespace App\Tallier\Output;


/**
 * A count of the votes.
 * The value are floats instead of integers because we may have complex tallybots with transitivity friction|entropy.
 * The purpose of a TallyBot is to generate and output an instance of this.
 */
class PollTally
{
    /**
     * @var array|PollProposalTally[]
     */
    public $proposals;

    /**
     * Tally constructor.
     * @param array $proposals
     */
    public function __construct(array $proposals)
    {
        $this->proposals = $proposals;
    }

    public function countVotes()
    {
        $count = 0;
        foreach ($this->proposals as $proposal) {
            $count += $proposal->countVotes();
        }

        return $count;
    }
}