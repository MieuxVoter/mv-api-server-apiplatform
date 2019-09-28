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
     * @var array|LimajuPollOptionTally[]
     */
    public $options;

    /**
     * Tally constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }
}