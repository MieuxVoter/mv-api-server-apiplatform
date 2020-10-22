<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Poll\Tally;
use App\Repository\PollRepository;
use App\Tallier\TallierFactory;
use App\Tallier\TallierInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * See App\Entity\Poll\Tally where this controller is declared.
 */
final class GetTallyController
{
    public function __invoke(
        Request $request,
        MessageBusInterface $bus,
        PollRepository $pollRepository,
        EntityManagerInterface $em,
        TallierFactory $tallierFactory
    ): Response {

        $pollId = $request->get('id');
        $poll = $pollRepository->findOneByUuid($pollId);

        if (null == $poll) {
            throw new NotFoundHttpException("Poll `$pollId' was not found.");
        }

        $tally = new Tally();

        $tallierType = "standard";
        $tallier = $tallierFactory->findByName($tallierType);
        $tallyOutput = $tallier->tallyVotesOnPoll($poll);

        $votesCount = $tallyOutput->countVotes();

        if (0 == $votesCount) {
            $error = "api.tallying.failure.generic";
            return new JsonResponse(['error' => $error], Response::HTTP_BAD_REQUEST);
        }

        $tally->standard = $tallyOutput;

        return new JsonResponse($tally, Response::HTTP_OK);
    }
}
