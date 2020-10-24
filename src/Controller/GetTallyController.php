<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Is\EntityAware;
use App\Entity\Poll\Proposal\Tally as ProposalTally;
use App\Entity\Poll\Tally;
use App\Tallier\TallierFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


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

        /// The output of the tallier is in another data model.
        /// We grab it convert it here to the API model.
        /// The Tallier heavily relies on methods in its data output classes.
        /// We could perhaps refactor the Talliers to directly output the API model Tally,
        /// by moving all the calculus logic somewhere else, in Traits of Talliers perhaps?
        /// Then we could remove half the lines in this controller, including this comment. :]

        $tallierAlgorithm = "standard";  // get it from request, but sanitize first!
        $tallier = $tallierFactory->findByName($tallierAlgorithm);
        $tallyRaw = $tallier->tally($poll);

        $leaderboard = [];
        foreach ($tallyRaw->proposals as $proposalOutput) {
            $proposalTally = new ProposalTally();
            $proposalUuid = $proposalOutput->getPollProposalId()->toString();
            $proposal = $this->getProposalRepository()->findOneByUuid($proposalUuid);
            $medianGrade = $this->getGradeRepository()->findOneByUuid($proposalOutput->getMedian());
            $proposalTally->setProposal($proposal);
            $proposalTally->setRank($proposalOutput->getRank());
            $proposalTally->setMedianGrade($medianGrade);
            $leaderboard[] = $proposalTally;
        }

        $tally = new Tally();
        $tally->setPoll($poll);
        $tally->setAlgorithm($tallierAlgorithm);
        $tally->setLeaderboard($leaderboard);

        return $tally;
    }
}
