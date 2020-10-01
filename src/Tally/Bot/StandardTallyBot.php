<?php


namespace App\Tally\Bot;


use App\Application;
use App\Entity\Poll;
use App\Entity\PollCandidateVote;
use App\Repository\PollCandidateRepository;
use App\Repository\PollCandidateVoteRepository;
use App\Tally\Output\LimajuPollCandidateTally;
use App\Tally\Output\LimajuPollTally;


/**
 * Not sure about the name "standard".
 *
 * - Sum direct votes
 * - Sum delegated votes without entropy
 *
 * Class StandardTallyBot
 * @package App\Tally\Bot
 */
class StandardTallyBot implements TallyBotInterface
{

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var PollCandidateRepository
     */
    protected $limajuPollCandidateRepository;

    /**
     * @var PollCandidateVoteRepository
     */
    protected $limajuPollCandidateVoteRepository;


    /**
     * StandardTallyBot constructor.
     * @param PollCandidateRepository $limajuPollCandidateRepository
     * @param PollCandidateVoteRepository $limajuPollCandidateVoteRepository
     * @param Application $application
     */
    public function __construct(
        PollCandidateRepository $limajuPollCandidateRepository,
        PollCandidateVoteRepository $limajuPollCandidateVoteRepository,
        Application $application)
    {
        $this->application = $application;
        $this->limajuPollCandidateRepository = $limajuPollCandidateRepository;
        $this->limajuPollCandidateVoteRepository = $limajuPollCandidateVoteRepository;
    }


    /**
     * @inheritDoc
     */
    public function tallyVotesOnLimajuPoll(Poll $poll): LimajuPollTally
    {
        /** @var LimajuPollCandidateTally[] $candidatesTallies */
        $candidatesTallies = array();

        $positions = (new LimajuPollCandidateTally())->getMentionsPositions();

        $maxVotesCount = 0;

        // First loop: collect data
        foreach ($poll->getCandidates() as $candidate) {

            $votes = $this->limajuPollCandidateVoteRepository->findBy([
                'candidate' => $candidate->getId(),
            ]);

            $votesCount = count($votes);
            $maxVotesCount = max($maxVotesCount, $votesCount);
            $mentionsTally = array(); // MENTION_XXX => integer

            if ($votesCount) {

                usort($votes, function(PollCandidateVote $a, PollCandidateVote $b) use ($positions) {
                    return $positions[$a->getMention()] - $positions[$b->getMention()];
                });

                foreach ($positions as $mentionToTally => $whoCares) {
                    $votesForMention = array_filter($votes, function(PollCandidateVote $v) use ($mentionToTally) {
                        return $v->getMention() === $mentionToTally;
                    });
                    $mentionsTally[$mentionToTally] = count($votesForMention);
                }

            }

            $candidateTally = new LimajuPollCandidateTally();
            $candidateTally->setPollCandidateId($candidate->getId());
            $candidateTally->setMentionsTally($mentionsTally);
            // Setting these later once we have all the tallies
            //$candidateTally->setMention(?);
            //$candidateTally->setPosition(?);

            $candidatesTallies[] = $candidateTally;
        }

        // Second loop: equalize the votes count and compute the mention
        foreach ($candidatesTallies as $candidateTally) {
            // Fill up candidate tallies that have less votes, with TO_REJECT mentions
            // so that all tallies have the same number of mentions in the end.
            // The goal here is to enforce the Rule about TO_REJECT being the default mention.
            $candidateTally->addVotesForMention($maxVotesCount - $candidateTally->countVotes(), Poll::MENTION_TO_REJECT);
            // Once this is done, we can now compute the final mention from the median
            $candidateTally->setMention($candidateTally->getMedian());
        }

        // Sort the candidates using majority judgment on the median
        usort($candidatesTallies, function(LimajuPollCandidateTally $a, LimajuPollCandidateTally $b) use ($positions) {
            // From https://en.wikipedia.org/wiki/Majority_judgment
            // If more than one candidate has the same highest median-grade,
            // the MJ winner is discovered by removing (one-by-one) any grades equal
            // in value to the shared median grade from each tied candidate’s total.
            // This is repeated until only one of the previously tied candidates
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
                while ( ! ($wipTallyA->isEmpty() && $wipTallyB->isEmpty())) {
                    $mentionA = $wipTallyA->getMedian();
                    $mentionB = $wipTallyB->getMedian();

                    if ($mentionA === $mentionB) {
                        $wipTallyA->removeOneVoteForMention($mentionA);
                        $wipTallyB->removeOneVoteForMention($mentionB);
                    } else {
                        return $positions[$mentionB] - $positions[$mentionA];
                    }
                }

                // All the votes were the exact same.  Banana condition.
                // Right now we're sorting in the order of the candidates, I think. To test.
                return 0;
            }

            return $positions[$b->getMedian()] - $positions[$a->getMedian()];
        });

        foreach ($candidatesTallies as $k => $candidateTally) {
            // In the future, two candidates may have the same position ; this code will perhaps change.
            $candidateTally->setPosition($k+1);
        }

        $tally = new LimajuPollTally($candidatesTallies);

        return $tally;
    }


}