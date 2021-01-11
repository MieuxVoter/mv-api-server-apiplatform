<?php


namespace MieuxVoter\MajorityJudgment\Model\Result;


/**
 * This is the output of a DeliberatorInterface.
 * This is essentially a Data Transfer Object (DTO) interface ?
 * It holds a ranking of the Proposals (a leaderboard?).
 * Will probably also hold other extra info about the poll results.
 *
 * Interface PollResultInterface
 * @package MieuxVoter\MajorityJudgment\Result
 */
interface PollResultInterface
{
    /**
     * TBD: rename into getLeaderboard ?
     *
     * These results are ordered by rank, "best" first.
     * Two or more results may share the same rank, in extreme (low-participation) cases,
     * if they exhibit the exact same merit profiles.
     * In this case, they are in the order the proposals were added.
     *
     * @return ProposalResult[]
     */
    public function getProposalResults() : iterable;
}