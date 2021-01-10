<?php


namespace MieuxVoter\MajorityJudgment\Model\Result;


/**
 * This is the output of a DeliberatorInterface.
 * It holds a ranking of the Proposals (a leaderboard).
 *
 * Interface PollResultInterface
 * @package MieuxVoter\MajorityJudgment\Result
 */
interface PollResultInterface
{
    /**
     * TBD: rename into getLeaderboard ?
     *
     * @return RankedProposal[]
     */
    public function getRankedProposals() : iterable;
}