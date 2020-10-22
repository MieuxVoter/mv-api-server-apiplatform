<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Poll\Tally;
use App\Repository\PollRepository;
use App\Tallier\TallyBotInterface;
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
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * GetTallyController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(
        Request $request,
        MessageBusInterface $bus,
        PollRepository $pollRepository,
        EntityManagerInterface $em
    ): Response {

        $pollId = $request->get('id');
        $poll = $pollRepository->findOneByUuid($pollId);

        if (null == $poll) {
            throw new NotFoundHttpException("Poll `$pollId' was not found.");
        }

        $tally = new Tally();

        $tallyType = "standard";
        $tallyBot = $this->getTallyBot($tallyType);
        $tallyOutput = $tallyBot->tallyVotesOnPoll($poll);

        $votesCount = $tallyOutput->countVotes();

        if (0 == $votesCount) {
            $error = "api.tallying.failure.generic";
            return new JsonResponse(['error' => $error], Response::HTTP_BAD_REQUEST);
        }

        $tally->standard = $tallyOutput;

        return new JsonResponse($tally, Response::HTTP_OK);
    }

    /**
     * @param string $tallyName
     * @return TallyBotInterface
     */
    protected function getTallyBot(string $tallyName) : TallyBotInterface
    {
        $tallyFileName = ucwords($tallyName);
        /** @noinspection MissingService */
        /** @noinspection CaseSensitivityServiceInspection */
        return $this->container->get("App\\Tallier\\${tallyFileName}TallyBot");
    }

}
