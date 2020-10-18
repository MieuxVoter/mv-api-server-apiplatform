<?php


namespace App\Handler;


use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\PollProposalVote;
use App\Entity\User;


/**
 * Exists to patch https://github.com/api-platform/docs/issues/504
 * Subresources should handle POST.  Since they don't yet, we do this "by hand".
 *
 * Class PollProposalVoteHandler
 * @package App\Handler
 */
class PollProposalVoteHandler
{

    public function handleVote(PollProposalVote $vote, User $judge, Proposal $proposal, Poll $poll)
    {
//        $vote->setProposal($proposal);
        // Instead we use the inverse, it sets both
        $proposal->addVote($vote);
        $vote->setElector($judge);
        return $vote;
    }

}