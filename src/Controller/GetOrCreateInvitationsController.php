<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\Poll\Invitation;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Proposal\Ballot;
use App\Handler\BallotHandler;
use App\Repository\PollInvitationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;


/**
 * TODO
 *
 * See App\Entity\Poll\Invitation where this controller is declared and configured.
 *
 * Class GetOrCreateInvitationsController
 * @package App\Controller
 */
class GetOrCreateInvitationsController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->em = $entityManager;
        $this->security = $security;
    }

    /**
     * @param Request $request
     */
    public function __invoke(Request $request): array
    {
        /** @var PollInvitationRepository $invitationsRepo */
        $invitationsRepo = $this->em->getRepository(Invitation::class);
        $pollId = $request->get("pollId");
        /** @var Poll $poll */
        $poll = $this->em->getRepository(Poll::class)->findOneByUuid($pollId);

        // FIXME:
        // Read existing Invitations
        // Generate missing Invitations

        return $this->em->getRepository(Invitation::class)->findAll();
    }
}
