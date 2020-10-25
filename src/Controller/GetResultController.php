<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Is\EntityAware;
use App\Entity\Poll\Grade\Result as ProposalGradeResult;
use App\Entity\Poll\Proposal\Result as ProposalResult;
use App\Entity\Poll\Result;
use App\Tallier\TallierFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * See App\Entity\Poll\Result where this controller is declared.
 */
final class GetResultController
{
    use EntityAware;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->setEm($entityManager);
    }

    public function __invoke(
        Request $request,
        TallierFactory $tallierFactory
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

        /// The output of the tallier is in another data model.
        /// We grab it convert it here to the API model.
        /// The Tallier heavily relies on methods in its data output classes.
        /// We could perhaps refactor the Talliers to directly output the API model Result,
        /// by moving all the calculus logic somewhere else, in Traits of Talliers perhaps?
        /// Then we could remove half the lines in this controller, including this comment. :]

        $tallierAlgorithm = "standard";  // get it from request, but sanitize first!
        $tallier = $tallierFactory->findByName($tallierAlgorithm);
        $resultRaw = $tallier->tally($poll);
        $grades = $poll->getGradesInOrder();

        $leaderboard = [];
        foreach ($resultRaw->proposals as $proposalOutput) {
            $proposalResult = new ProposalResult();
            $proposalUuid = $proposalOutput->getPollProposalId()->toString();
            $proposal = $this->getProposalRepository()->findOneByUuid($proposalUuid);
            $medianGrade = $this->getGradeRepository()->findOneByUuid($proposalOutput->getMedian());
            $proposalResult->setProposal($proposal);
            $proposalResult->setRank($proposalOutput->getRank());
            $proposalResult->setMedianGrade($medianGrade);
            $proposalResult->setTally($proposalOutput->countVotes());

            foreach ($grades as $grade) {

                $gradeResult = new ProposalGradeResult();
                $gradeResult->setGrade($grade);
                $gradeResult->setProposal($proposal);
                $gradeResult->setTally($proposalOutput->getGradesTally()[$grade->getUuid()->toString()]);
                $proposalResult->addGradeResult($gradeResult);
            }

            $leaderboard[] = $proposalResult;
        }

        $result = new Result();
        $result->setPoll($poll);
        $result->setAlgorithm($tallierAlgorithm);
        $result->setLeaderboard($leaderboard);

        return $result;
    }
}
