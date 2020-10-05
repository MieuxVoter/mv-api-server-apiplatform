<?php


namespace App\Tally\Output;


/**
 * NOT USED
 *
 * A count of the votes.
 * The value are floats instead of integers because we may have complex tallybots with transitivity friction|entropy.
 * The purpose of a TallyBot is to generate and output an instance of this.
 */
class Tally
{
    /**
     * @var float
     */
    protected $inFavor;

    /**
     * @var float
     */
    protected $against;

    /**
     * Tally constructor.
     *
     * @param float $inFavor
     * @param float $against
     */
    public function __construct(float $inFavor, float $against)
    {
        $this->inFavor = $inFavor;
        $this->against = $against;
    }

    /**
     * @return float
     */
    public function getInFavor(): float
    {
        return $this->inFavor;
    }

    /**
     * @return float
     */
    public function getAgainst(): float
    {
        return $this->against;
    }
}