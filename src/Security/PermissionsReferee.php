<?php

namespace App\Security;

use App\Controller\Is\UserAware;
use App\Entity\Poll;
use App\Entity\Poll\Invitation;
use App\Entity\User;


class PermissionsReferee
{
    use UserAware;

    /**
     * @see canUserGenerateInvitationsFor
     * @param Poll $poll
     * @return bool
     */
    public function canGenerateInvitationsFor(?Poll $poll) {
        return $this->canUserGenerateInvitationsFor($this->getUser(), $poll);
    }

    /**
     * Rule: You can only generate invitations if you are the author of the poll
     * (We'd love to add Organizers, or "recursive" invitations)
     *
     * @param User $user
     * @param Poll $poll
     * @return bool
     */
    public function canUserGenerateInvitationsFor(?User $user, ?Poll $poll)
    {
        if ((null == $user) || (null == $poll)) {
            return false;
        }

        if ($poll->getAuthor() === $user) {
            return true;
        }

        return false;
    }

    public function isInvitationAcceptedByYou(Invitation $invitation) : BOOL
    {
        $user = $this->getUser();
        if (null === $user) {
            return false;
        }

        return $invitation->getAuthor() === $user;
    }
}