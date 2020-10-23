<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Is\EntityAware;
use App\Entity\Poll\Tally;
use App\Repository\PollRepository;
use App\Tallier\TallierFactory;
use App\Tallier\TallierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * See App\Entity\Poll\Tally where this controller is declared.
 */
final class GetTallyController
{
    use EntityAware;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->setEm($entityManager);
    }

    public function __invoke(
        Request $request,
        TallierFactory $tallierFactory
    ) : Tally {

        $pollId = $request->get('id');
        $poll = $this->getPollRepository()->findOneByUuid($pollId);

        if (null == $poll) {
            throw new NotFoundHttpException("Poll `$pollId' was not found.");
        }

        if (0 === $this->getBallotRepository()->countPollBallots($poll)) {
            throw new HttpException(
                Response::HTTP_PRECONDITION_FAILED, // To Review
                "api.result.error.poll.empty"
            );
        }

        $tallierAlgorithm = "standard";  // get it from request, but sanitize first!
        $tallier = $tallierFactory->findByName($tallierAlgorithm);
//        $tallyOutput = $tallier->tallyVotesOnPoll($poll);

        $leaderboard = [];  // FIXME: fill this

        $result = new Tally();
        $result->setPoll($poll);
        $result->setAlgorithm($tallierAlgorithm);
        $result->setLeaderboard($leaderboard);

        return $result;
    }
}
