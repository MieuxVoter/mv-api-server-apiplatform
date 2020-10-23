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
    public function tallyVotesOnPoll(Poll $poll): PollTally
    {
        /** @var PollProposalTally[] $proposalsTallies */
        $proposalsTallies = array();

        $defaultGrade = $poll->getDefaultGradeName();
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

//            if ($votesCount) {

            usort($votes, function (Ballot $a, Ballot $b) use ($levelOfGrade) {
                return $levelOfGrade[$a->getGrade()] - $levelOfGrade[$b->getGrade()];
            });

            foreach ($levelOfGrade as $gradeToTally => $whoCares) {
                $votesForMention = array_filter($votes, function (Ballot $v) use ($gradeToTally) {
                    return $v->getGrade() === $gradeToTally;
                });
                $gradesTally[$gradeToTally] = count($votesForMention);
            }

//            }

            $proposalTally = new PollProposalTally();
            $proposalTally->setPollProposalId($proposal->getUuid());
            $proposalTally->setGradesNames($poll->getGradesNames());
            $proposalTally->setGradesTally($gradesTally);
            // Setting these later once we have all the tallies
            //$proposalTally->setMention(?);
            //$proposalTally->setPosition(?);

            $proposalsTallies[] = $proposalTally;
        }

        // Second loop: equalize the votes count and compute the mention
        foreach ($proposalsTallies as $proposalTally) {
            // Fill up proposal tallies that have less votes, with TO_REJECT mentions
            // so that all tallies have the same number of mentions in the end.
            // The goal here is to enforce the Rule about TO_REJECT being the default mention.
            $proposalTally->addVotesForGrade($maxVotesCount - $proposalTally->countVotes(), $defaultGrade);
            // Once this is done, we can now compute the final mention from the median
            $proposalTally->setMedianGrade($proposalTally->getMedian());
        }

        // Sort the proposals using majority judgment on the median
        usort($proposalsTallies, function (PollProposalTally $a, PollProposalTally $b) use ($levelOfGrade) {
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

                // What should we do with these? Let's spec this later.
                // Current code already considers that no-votes are TO_REJECT.
                // Note that assert() appears be ineffective (for now) in our test-suite.
                //assert($wipTallyA->countVotes() === $wipTallyB->countVotes());

                // While one may prefer recursive functions for their simplicity,
                // we're approaching this with a flat loop that should scale RAM usage better.
                // Of course, it may still blow into infinite loops.  Those are the best.
                while (!($wipTallyA->isEmpty() && $wipTallyB->isEmpty())) {
                    $mentionA = $wipTallyA->getMedian();
                    $mentionB = $wipTallyB->getMedian();

                    if ($mentionA === $mentionB) {
                        $wipTallyA->removeOneVoteForMention($mentionA);
                        $wipTallyB->removeOneVoteForMention($mentionB);
                    } else {
                        return $levelOfGrade[$mentionB] - $levelOfGrade[$mentionA];
                    }
                }

                // All the votes were the exact same.  Banana condition.
                // Right now we're sorting in the order of the proposals, I think. To test.
                return 0;
            }

            return $levelOfGrade[$b->getMedian()] - $levelOfGrade[$a->getMedian()];
        });

        foreach ($proposalsTallies as $k => $proposalTally) {
            // In the future, two proposals may have the same position ; this code will perhaps change.
            $proposalTally->setRank($k + 1);
        }

        $tally = new PollTally($proposalsTallies);

        return $tally;
    }


}