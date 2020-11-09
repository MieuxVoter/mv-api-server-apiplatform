<?php


namespace App\Tallier;


use App\Entity\Poll;
use App\Tallier\Output\PollTally;


/**
 * The purpose of a Tallier is to create a PollTally from the Ballots (and perhaps Delegations).
 *
 * Since there is a ocean of possibilities when tallying, you may implement your own TallyBot.
 * It will only need to implement this interface (and perhaps be declared as Service with a special tag).
 *
 * For now, please put your implementations in the same directory as this interface,
 * and suffix them with `Tallier`, like `MyBetterTallier`
 */
interface TallierInterface
{
    public function tally(Poll $poll) : PollTally;
}