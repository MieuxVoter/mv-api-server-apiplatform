<?php


namespace App\Features;


use App\Entity\Poll;


/**
 * Actor things that are specific to that application.
 *
 * Class Actor
 * @package App\Features
 */
class Actor extends ApiActor
{

    /** @var array PollStringUuid => Array of invitation-like associative array */
    public $invitations = [];

    public function addInvitation($invitation, Poll $poll)
    {
        $pollId = $poll->getUuid()->toString();

        if ( ! isset($this->invitations[$pollId])) {
            $this->invitations[$pollId] = [];
        }

        $this->invitations[$pollId][] = $invitation;
    }

    public function countInvitations(?Poll $poll = null) : int
    {
        assert(null === $poll); // todo: filter per poll

        $amount = 0;
        foreach ($this->invitations as $pollId => $invitations) {
            $amount += count($invitations);
        }

        return $amount;
    }

}