<?php


namespace MieuxVoter\MajorityJudgment\Result;


/**
 * This is the output of a ResolverInterface.
 * It holds a ranking of the Proposals.
 *
 * Interface PollResultInterface
 * @package MieuxVoter\MajorityJudgment\Result
 */
interface PollResultInterface
{
    /**
     * @return RankedProposal[]
     */
    public function getRankedProposals() : iterable;
}