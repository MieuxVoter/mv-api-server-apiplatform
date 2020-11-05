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
        $pollId = $poll->getUuid();

        if ( ! isset($this->invitations[$pollId])) {
            $this->invitations[$pollId] = [];
        }

        if ( ! in_array($invitation, $this->invitations[$pollId])) {
            $this->invitations[$pollId][] = $invitation;
        }

    }

    public function countInvitations(?Poll $poll = null) : int
    {
        assert(null === $poll); // todo: filter per poll if specified

        $amount = 0;
        foreach ($this->invitations as $pollId => $invitations) {
            $amount += count($invitations);
        }

        return $amount;
    }

    public function getInvitationByNumber($index)
    {
        assert($index > 0);
        $current = 1;
        foreach ($this->invitations as $invitations) {
            // inefficient, can be optimized
            foreach ($invitations as $invitation) {
                if ($current == $index) {
                    return $invitation;
                }
                $current++;
            }
        }

        return null;
    }

}