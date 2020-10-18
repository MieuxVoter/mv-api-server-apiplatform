<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Proposal\Ballot;
use App\Handler\BallotHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Security
     */
    private $security;

    public function __construct(
        BallotHandler $ballotandler,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->ballotHandler = $ballotandler;
        $this->entityManager = $entityManager;
        $this->security = $security;
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
        $poll = $this->entityManager->getRepository(Poll::class)->findOneByUuid($pollId);
        $proposal = $this->entityManager->getRepository(Proposal::class)->findOneByUuid($proposalId);
        $judge = $this->security->getUser();
        $ballot = $this->ballotHandler->handleVote($data, $judge, $proposal, $poll);

        return $ballot;
    }
}
