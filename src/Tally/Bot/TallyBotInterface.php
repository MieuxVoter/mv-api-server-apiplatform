<?php


namespace App\Tally\Bot;


use App\Entity\Poll;
use App\Tally\Output\LimajuPollTally;


/**
 * The purpose of a TallyBot is to create a Tally from the Votes and Delegations.
 *
 * Since there is a ocean of possibilities when tallying, you may implement your own TallyBot.
 * It will only need to implement this interface (and perhaps be declared as Service with a special tag).
 *
 * For now, please put your implementations in the same directory as this interface,
 * and suffix them with `TallyBot`, like `NaiveTallyBot`
 */
interface TallyBotInterface
{
    public function tallyVotesOnLimajuPoll(Poll $poll) : LimajuPollTally;
}