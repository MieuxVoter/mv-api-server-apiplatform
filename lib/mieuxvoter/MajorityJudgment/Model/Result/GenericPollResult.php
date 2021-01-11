<?php


namespace MieuxVoter\MajorityJudgment\Model\Result;


/**
 * Generic, instantiable implementation of PollResultInterface.
 * This is a convenience class.
 *
 * Class GenericPollResult
 * @package MieuxVoter\MajorityJudgment\Result
 */
class GenericPollResult implements PollResultInterface
{

    protected $ranked_proposals = [];

    /**
     * GenericPollResult constructor.
     * @param array $ranked_proposals
     */
    public function __construct(iterable $ranked_proposals)
    {
        $this->ranked_proposals = $ranked_proposals;
    }

    /**
     * @return ProposalResult[]
     */
    public function getProposalResults(): iterable
    {
        return $this->ranked_proposals;
    }
}