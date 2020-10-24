<?php


namespace App\Tallier;


use App\Application;
use App\Entity\Poll;
use App\Entity\Poll\Proposal\Ballot;
use App\Repository\PollProposalRepository;
use App\Repository\PollProposalBallotRepository;
use App\Tallier\Output\PollProposalTally;
use App\Tallier\Output\PollTally;


/**
 * Not sure about the name "standard".
 * Pretty inefficient algorithm.
 *
 * Sums direct votes.
 */
class StandardTallier implements TallierInterface
{

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var PollProposalRepository
     */
    protected $pollProposalRepository;

    /**
     * @var PollProposalBallotRepository
     */
    protected $pollProposalVoteRepository;


    /**
     * StandardTallier constructor.
     * @param PollProposalRepository $pollProposalRepository
     * @param PollProposalBallotRepository $pollProposalVoteRepository
     * @param Application $application
     */
    public function __construct(
        PollProposalRepository $pollProposalRepository,
        PollProposalBallotRepository $pollProposalVoteRepository,
        Application $application
    )
    {
        $this->application = $application;
        $this->pollProposalRepository = $pollProposalRepository;
        $this->pollProposalVoteRepository = $pollProposalVoteRepository;
    }


    /**
     * @inheritDoc
     */
    public function tally(Poll $poll): PollTally
    {
        /** @var PollProposalTally[] $proposalsTallies */
        $proposalsTallies = array();

        $defaultGrade = $poll->getDefaultGradeUuid();
        $levelOfGrade = $poll->getLevelsOfGrades();
        $maxVotesCount = 0;

        // First loop: collect data
        foreach ($poll->getProposals() as $proposal) {

            $votes = $this->pollProposalVoteRepository->findBy([
                'proposal' => $proposal->getId(),
            ]);

            $votesCount = count($votes);
            $maxVotesCount = max($maxVotesCount, $votesCount);
            $gradesTally = array(); // grade_name => integer

            usort($votes, function (Ballot $a, Ballot $b) use ($levelOfGrade) {
                return (
                    $levelOfGrade[$a->getGrade()->getUuid()->toString()]
                    -
                    $levelOfGrade[$b->getGrade()->getUuid()->toString()]
                );
            });

            foreach ($levelOfGrade as $gradeToTallyUuid => $whoCares) {
                $votesForMention = array_filter($votes, function (Ballot $v) use ($gradeToTallyUuid) {
                    return $v->getGrade()->getUuid()->toString() === $gradeToTallyUuid;
                });
                $gradesTally[$gradeToTallyUuid] = count($votesForMention);
            }

            $proposalTally = new PollProposalTally();
            $proposalTally->setPollProposalId($proposal->getUuid());
            $proposalTally->setGradesUuids($poll->getGradesUuids());
            $proposalTally->setGradesTally($gradesTally);
            // Setting these later once we have all the tallies
            //$proposalTally->setGrade(?);
            //$proposalTally->setRank(?);

            $proposalsTallies[] = $proposalTally;
        }

        // Second loop: equalize the votes count and compute the grade
        foreach ($proposalsTallies as $proposalTally) {
            // Fill up proposal tallies that have less votes, with TO_REJECT grades
            // so that all tallies have the same number of grades in the end.
            // The goal here is to enforce the Rule about TO_REJECT being the default grade.
            $proposalTally->addVotesForGrade($maxVotesCount - $proposalTally->countVotes(), $defaultGrade);
            // Once this is done, we can now compute the final grade from the median
            $proposalTally->setMedianGrade($proposalTally->getMedian());
        }

        // Sort the proposals using majority judgment on the median
        // This is very naive and won't scale well
        $compareFunction = function (PollProposalTally $a, PollProposalTally $b) use ($levelOfGrade) {
            // From https://en.wikipedia.org/wiki/Majority_judgment
            // If more than one proposal has the same highest median-grade,
            // the MJ winner is discovered by removing (one-by-one) any grades equal
            // in value to the shared median grade from each tied proposal’s total.
            // This is repeated until only one of the previously tied proposals
            // is currently found to have the highest median-grade.
            if ($a->getMedian() === $b->getMedian()) {
                // We're going to work on copies we can remove votes from.
                $wipTallyA = clone $a;
                $wipTallyB = clone $b;

                // While one may prefer recursive functions for their simplicity,
                // we're approaching this with a flat loop that should scale RAM usage better.
                // Of course, it may still blow into infinite loops.  Those are the best.
                while (!($wipTallyA->isEmpty() && $wipTallyB->isEmpty())) {
                    $gradeA = $wipTallyA->getMedian();
                    $gradeB = $wipTallyB->getMedian();

                    if ($gradeA === $gradeB) {
                        $wipTallyA->removeOneVoteForMention($gradeA);
                        $wipTallyB->removeOneVoteForMention($gradeB);
                    } else {
                        return $levelOfGrade[$gradeB] - $levelOfGrade[$gradeA];
                    }
                }

                // All the votes were the exact same.  Banana condition.
                // Right now we're sorting in the order of the proposals, I think. To test.
                return 0;
            }

            return $levelOfGrade[$b->getMedian()] - $levelOfGrade[$a->getMedian()];
        };

        usort($proposalsTallies, $compareFunction);

        // Two proposals may have the same rank, in extreme cases
        $previousRank = 0;
        foreach ($proposalsTallies as $k => $proposalTally) {
            $rank = $k + 1;
            if (
                ($k > 0)
                &&
                (0 === $compareFunction($proposalsTallies[$k-1], $proposalsTallies[$k]))
            ) {
                $rank = $previousRank;
            }
            $proposalTally->setRank($rank);
            $previousRank = $rank;
        }

        $tally = new PollTally($proposalsTallies);

        return $tally;
    }


}