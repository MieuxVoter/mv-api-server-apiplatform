<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Tally;
use App\Repository\PollRepository;
use App\Tally\Bot\TallyBotInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Old thing.
 * How can we re-do this more in line with apiplatform?
 *
 * → DataProvider
 *
 * @Route("/api/polls/{id}/tally", name="api_poll_tally_get", methods={"GET"})
 */
final class GetTallyController
{
    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * GetTallyController constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function __invoke(
        string $id,
        Request $request,
        MessageBusInterface $bus,
        PollRepository $pollRepository,
        EntityManagerInterface $em
    ): Response {

        $poll = $pollRepository->findOneByUuid($id);

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
        return $this->container->get("App\\Tally\\Bot\\${tallyFileName}TallyBot");
    }

}
