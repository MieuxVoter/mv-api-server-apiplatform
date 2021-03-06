<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Is\EntityAware;
use App\Entity\Poll\Result;
use App\Ranking\Settings\MajorityJudgmentSettings;
use App\Ranking\Rankings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Computes the result of a poll stored in the database.
 *
 * See App\Entity\Poll\Result where this controller is declared in annotations.
 */
final class GetResultController
{
    use EntityAware;

    public function __invoke(
        Request $request,
        Rankings $rankings
    ): Result
    {
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

        // The ranking algorithm parameter may be publicly exposed in the future
        $rankingAlgorithmName = "Majority Judgment";
        $ranking = $rankings->findByName($rankingAlgorithmName);

        $settings = new MajorityJudgmentSettings();

        return $ranking->resolve($poll, $settings);
    }
}
