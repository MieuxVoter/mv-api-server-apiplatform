<?php


namespace App\Tally\Bot;


use App\Application;
use App\Entity\LimajuPoll;
use App\Entity\LimajuPollOptionVote;
use App\Repository\LimajuPollOptionRepository;
use App\Repository\LimajuPollOptionVoteRepository;
use App\Tally\Output\LimajuPollOptionTally;
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
     * @var LimajuPollOptionRepository
     */
    protected $limajuPollOptionRepository;

    /**
     * @var LimajuPollOptionVoteRepository
     */
    protected $limajuPollOptionVoteRepository;


    /**
     * StandardTallyBot constructor.
     * @param LimajuPollOptionRepository $limajuPollOptionRepository
     * @param LimajuPollOptionVoteRepository $limajuPollOptionVoteRepository
     * @param Application $application
     */
    public function __construct(
        LimajuPollOptionRepository $limajuPollOptionRepository,
        LimajuPollOptionVoteRepository $limajuPollOptionVoteRepository,
        Application $application)
    {
        $this->application = $application;
        $this->limajuPollOptionRepository = $limajuPollOptionRepository;
        $this->limajuPollOptionVoteRepository = $limajuPollOptionVoteRepository;
    }


    /**
     * @inheritDoc
     */
    public function tallyVotesOnLimajuPoll(LimajuPoll $poll): LimajuPollTally
    {
        /** @var LimajuPollOptionTally[] $optionsTallies */
        $optionsTallies = array();

        $positions = (new LimajuPollOptionTally())->getMentionsPositions();

        $maxVotesCount = 0;

        // First loop: collect data
        foreach ($poll->getOptions() as $option) {

            $votes = $this->limajuPollOptionVoteRepository->findBy([
                'option' => $option->getId(),
            ]);

            $votesCount = count($votes);
            $maxVotesCount = max($maxVotesCount, $votesCount);
            $mentionsTally = array(); // MENTION_XXX => integer

            if ($votesCount) {

                usort($votes, function(LimajuPollOptionVote $a, LimajuPollOptionVote $b) use ($positions) {
                    return $positions[$a->getMention()] - $positions[$b->getMention()];
                });

                foreach ($positions as $mentionToTally => $whoCares) {
                    $votesForMention = array_filter($votes, function(LimajuPollOptionVote $v) use ($mentionToTally) {
                        return $v->getMention() === $mentionToTally;
                    });
                    $mentionsTally[$mentionToTally] = count($votesForMention);
                }

            }

            $optionTally = new LimajuPollOptionTally();
            $optionTally->setPollOptionId($option->getId());
            $optionTally->setMentionsTally($mentionsTally);
            // Setting these later once we have all the tallies
            //$optionTally->setMention(?);
            //$optionTally->setPosition(?);

            $optionsTallies[] = $optionTally;
        }

        // Second loop: equalize the votes count and compute the mention
        foreach ($optionsTallies as $optionTally) {
            // Fill up option tallies that have less votes, with TO_REJECT mentions
            // so that all tallies have the same number of mentions in the end.
            // The goal here is to enforce the Rule about TO_REJECT being the default mention.
            $optionTally->addVotesForMention($maxVotesCount - $optionTally->countVotes(), LimajuPoll::MENTION_TO_REJECT);
            // Once this is done, we can now compute the final mention from the median
            $optionTally->setMention($optionTally->getMedian());
        }

        // Sort the options using majority judgment on the median
        usort($optionsTallies, function(LimajuPollOptionTally $a, LimajuPollOptionTally $b) use ($positions) {
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
                // Right now we're sorting in the order of the options, I think. To test.
                return 0;
            }

            return $positions[$b->getMedian()] - $positions[$a->getMedian()];
        });

        foreach ($optionsTallies as $k => $optionTally) {
            // In the future, two options may have the same position ; this code will perhaps change.
            $optionTally->setPosition($k+1);
        }

        $tally = new LimajuPollTally($optionsTallies);

        return $tally;
    }


}