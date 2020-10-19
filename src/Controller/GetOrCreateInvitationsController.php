<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\Poll\Invitation;
use App\Entity\Poll\Proposal;
use App\Repository\PollInvitationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;


/**
 * Trying the "eager getter" pattern.  Create invitations in the database, on-demand.
 * We should (at least sometimes) not let clients POST invitations,
 * since we also want mass-email-based invitations
 * where even the organizer cannot access invitation tokens. (for added trustworthiness)
 *
 * See App\Entity\Poll\Invitation where this controller is declared and configured.
 *
 * Class GetOrCreateInvitationsController
 * @package App\Controller
 */
class GetOrCreateInvitationsController
{
    use Is\EntityManagerAware;
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
     * @return array
     */
    public function __invoke(Request $request): array
    {
        $pollId = $request->get("pollId");
        /** @var PollInvitationRepository $invitationsRepo */
        $invitationsRepo = $this->em->getRepository(Invitation::class);
        /** @var Poll $poll */
        $poll = $this->em->getRepository(Poll::class)->findOneByUuid($pollId);

        $maximumLimit = 500; // app config?
        $defaultLimit = 10;  // idem
        $limit = $request->get("limit", $defaultLimit);
        $limit = clamp(0, $maximumLimit, $limit);
        $defaultOffset = 0;
        $offset = 0; // todo

        $invitations = [];

        // Read existing Invitations
        // We could order them by creation date instead of numerical id
        $existingInvitations = $invitationsRepo->findBy(['poll'=>$poll], ['id' => 'ASC'], $limit, $offset);
        $invitations = $invitations + $existingInvitations;

        $missingInvitationsAmount = $limit - count($invitations);
        if ($missingInvitationsAmount > 0) {
            // Generate missing Invitations
            //
            // Database Concern(s)
            // -------------------
            // Let's limit the amount of invitations one can generate.
            // Admins may ignore that limit.
            // We might do things like allowing more invitations
            // once a quota of existing invitations has been "consumed".
            // Or allow overriding the quota from a user property or role.
            // Game design, etc.
            // Anyways, this need benchmarking.
            //
            // Security Concern(s)
            // -------------------
            // We're generating invitations in fast sequence,
            // and perhaps UUIDv4 pseudo-random could be reproduced to guess invitations.
            // UUIDv4 relies on random_bytes(), if I'm reading this right.
            // https://www.php.net/manual/en/function.random-bytes.php
            // … "cryptographically secure". Perhaps not enough for us (sequence!).  TBD
            for ($i = 0; $i < $missingInvitationsAmount; $i++) {
                $invitation = new Invitation();
                $invitation->setPoll($poll);
                $this->em->persist($invitation);
                $invitations[] = $invitation;
            }

            $this->em->flush();
        }

        return $invitations;
    }
}
