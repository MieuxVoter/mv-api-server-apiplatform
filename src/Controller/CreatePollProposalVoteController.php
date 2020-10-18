<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\PollProposalVote;
use App\Handler\PollProposalVoteHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;


/**
 * Exists to patch https://github.com/api-platform/docs/issues/504
 * Subresources should handle POST.  Since they don't yet, we do this "by hand".
 *
 * Class CreatePollProposalVoteController
 * @package App\Controller
 */
class CreatePollProposalVoteController
{
    protected $voteHandler;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Security
     */
    private $security;

    public function __construct(
        PollProposalVoteHandler $voteHandler,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->voteHandler = $voteHandler;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

//     * @Route(
//     *     name="api_poll_proposal_votes_post_collection",
//     *     path="/polls/{pollId}/proposals/{proposalId}/votes.{_format}",
//     *     methods={"POST"},
//     *     defaults={
//     *         "_api_resource_class"=PollProposalVote::class,
//     *         "_api_collection_operation_name"="post",
//     *     },
//     * )
    /**
     * @param PollProposalVote $data
     * @param Request $request
     * @return PollProposalVote
     */
    public function __invoke(PollProposalVote $data, Request $request): PollProposalVote
    {
        $pollId = $request->get("pollId");
        $proposalId = $request->get("proposalId");
        $poll = $this->entityManager->getRepository(Poll::class)->findOneByUuid($pollId);
        $proposal = $this->entityManager->getRepository(Proposal::class)->findOneByUuid($proposalId);
        $judge = $this->security->getUser();
        $vote = $this->voteHandler->handleVote($data, $judge, $proposal, $poll);

        return $vote;
    }
}
