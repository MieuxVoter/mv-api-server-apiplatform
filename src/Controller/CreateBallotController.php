<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Proposal\Ballot;
use App\Handler\BallotHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;


/**
 * Exists to patch https://github.com/api-platform/docs/issues/504
 * Subresources should handle POST.  Since they don't yet, we do this "by hand".
 *
 * See App\Entity\Poll\Proposal\Ballot where this controller is declared and configured.
 *
 * Class CreateBallotController
 * @package App\Controller
 */
class CreateBallotController
{
    protected $ballotHandler;

    use Is\EntityAware;
    use Is\UserAware;

    public function __construct(
        BallotHandler $ballotandler,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->ballotHandler = $ballotandler;
        $this->setEm($entityManager);
        $this->setSecurity($security);
    }

    /**
     * @param Ballot $data
     * @param Request $request
     * @return Ballot
     */
    public function __invoke(Ballot $data, Request $request): Ballot
    {
        $pollId = $request->get("pollId");
        $proposalId = $request->get("proposalId");
        /** @var Poll $poll */
        $poll = $this->getEm()->getRepository(Poll::class)->findOneByUuid($pollId);
        $proposal = $this->getEm()->getRepository(Proposal::class)->findOneByUuid($proposalId);
        $judge = $this->security->getUser();

        // Handles setting poll and proposal since apiplatform does not
        $ballot = $this->ballotHandler->handleVote($data, $judge, $proposal, $poll);

        // WiP – Another handler?  This time for scope access checks?
        if ($poll->getScope() === Poll::SCOPE_PRIVATE) {
            // fixme: check invitations
            throw new NotFoundHttpException("Nope");
        }

        return $ballot;
    }
}
