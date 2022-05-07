<?php

declare(strict_types=1);

namespace App\Controller;


use App\Entity\Poll\Invitation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * See \App\Entity\Poll\Invitation where this controller is declared and configured.
 *
 * Class AcceptInvitationController
 * @package App\Controller
 */
class AcceptInvitationController
{
    use Is\EntityAware;
    use Is\UserAware;

    /**
     * @param Request $request
     * @return Invitation|null
     */
    public function __invoke(Request $request)
    {
        $invitationIdentifier = $request->get('uuid', $request->get('id'));
        assert(!empty($invitationIdentifier), "Invitation Identifier should be read.");

        $invitationsRepo = $this->getInvitationRepository();
        $invitation = $invitationsRepo->findOneByUuid($invitationIdentifier);

        if (null === $invitation) {
            // TODO: Resilience
            // Use TrustWorth service or something akin
            // ->addSuspicion(new InvitationDiscoveryAbuse())
            throw new NotFoundHttpException();
        }

        if ($invitation->isAccepted()) {
            return $invitation;
        }

        $user = $this->getUser();

        if (null === $user) {
            return $invitation;
        }

        $invitation->setParticipant($user);
        $this->flush();

        return $invitation;
    }
}
