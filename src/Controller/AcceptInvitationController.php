<?php


namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;


/**
 * See App\Entity\Poll\Invitation where this controller is declared and configured.
 *
 * Class AcceptInvitationController
 * @package App\Controller
 */
class AcceptInvitationController
{
    use Is\EntityAware;
    use Is\UserAware;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->setEm($entityManager);
        $this->setSecurity($security);
    }

    /**
     * @param Request $request
     */
    public function __invoke(Request $request)
    {
        $invitationId = $request->get('id');
        $invitationsRepo = $this->getInvitationRepository();
        $invitation = $invitationsRepo->findOneByUuid($invitationId);

        if (null === $invitation) {
            // TODO: Resilience
            // Use TrustWorth service or something akin
            // ->addSuspicion(new InvitationDiscoveryAbuse())
            throw new NotFoundHttpException();
        }

        $user = $this->getUser();
        $invitation->setParticipant($user);
        $this->flush();

        return $invitation;
    }
}
