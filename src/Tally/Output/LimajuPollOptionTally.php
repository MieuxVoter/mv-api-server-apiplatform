<?php


namespace App\Tally\Output;


use App\Entity\LimajuPoll;
use Ramsey\Uuid\UuidInterface;


class LimajuPollOptionTally
{
    /**
     * @var UuidInterface
     * UUID of the LimajuPollOption that this tally belongs to.
     */
    public $poll_option_id;

    /**
     * @var string
     * Final mention tallied, for example the median mention in the standard tally.
     * One of LimajuPoll::MENTION_XXX
     */
    public $mention;

    /**
     * @var integer
     * The position of this option in its poll, after tallying.
     * The position starts at 1 and ends at <NUMBER_OF_OPTIONS>.
     * Two options MAY have the same position, in extreme cases.
     */
    public $position;

    /**
     * @var array
     * LimajuPoll::MENTION_XXX => integer
     * Count of votes for each mention.
     */
    public $mentions_tally;


    /**
     * @var array|string[]
     * The list of LimajuPoll::MENTION_XXX this tally uses.
     * The order matters, and must be from worse to best.
     */
    protected $mentions_list;
    // protected $mentions_tree;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return UuidInterface
     */
    public function getPollOptionId(): UuidInterface
    {
        return $this->poll_option_id;
    }

    /**
     * @param UuidInterface $poll_option_id
     */
    public function setPollOptionId(UuidInterface $poll_option_id): void
    {
        $this->poll_option_id = $poll_option_id;
    }

    /**
     * @return string
     */
    public function getMention(): string
    {
        return $this->mention;
    }

    /**
     * @param string $mention
     */
    public function setMention(string $mention): void
    {
        $this->mention = $mention;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return array
     */
    public function getMentionsTally(): array
    {
        return $this->mentions_tally;
    }

    /**
     * @param array $mentions_tally
     */
    public function setMentionsTally(array $mentions_tally): void
    {
        $this->mentions_tally = $mentions_tally;
    }


    /**
     * Only true when there are no votes whatsoever.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return 0 === $this->countVotes();
    }


    /**
     * Get the median mention. (one of `LimajuPoll::MENTION_XXX`)
     * When the count of votes is even, the lower|worse median is privileged.
     *
     * There are many ways to write such a method.
     * Current code is smelly.
     * Perhaps the code in here should be less candid with its input.
     *
     * @param bool $low In case of an even number of votes,
     *                  should we pick the lower median (default) or the higher median?
     * @return string Mention slug in esperanto (one of `LimajuPoll::MENTION_XXX`).
     */
    public function getMedian($low=true): string
    {
        $mentions = $this->getMentionsList();
        $order = $this->getMentionsPositions();
        $tally = $this->getMentionsTally();
        $count = $this->countVotes();

        $median = $mentions[0];  // Worse mention is the default.

        if (0 === $count) {
            return $median;
        }

        $medianIndex = (int) floor(($count + (($low) ? -1 : 1)) / 2.0);

        $current = 0;
        foreach ($mentions as $mention) { // worst to best
            $mentionMin = $current;
            $current += $tally[$mention];
            $mentionMax = $current;

            if ($mentionMin <= $medianIndex && $medianIndex < $mentionMax) {
                $median = $mention;
                break;
            }
        }

        return $median;
    }

    /**
     * @return array|string[]
     */
    public function getMentionsList()
    {
        if (null === $this->mentions_list) {
            // Let's initialize here the list of mentions.
            // What should we do with these? => Inject from Config?
            $this->mentions_list = [
                LimajuPoll::MENTION_TO_REJECT,
                LimajuPoll::MENTION_MEDIOCRE,
                LimajuPoll::MENTION_INADEQUATE,
                LimajuPoll::MENTION_PASSABLE,
                LimajuPoll::MENTION_GOOD,
                LimajuPoll::MENTION_VERY_GOOD,
                LimajuPoll::MENTION_EXCELLENT,
            ];
        }


        return $this->mentions_list;
    }


    /**
     * @param array|string[] $mentions_list
     */
    public function setMentionsList($mentions_list): void
    {
        $this->mentions_list = $mentions_list;
    }


    /**
     * Yields the mapping of the mentions to their "worth", an integer between 0 and `mentionsCount-1`.
     * Helps when sorting the options during tallying.
     *
     * @return array of MENTION_XXX => N
     */
    public function getMentionsPositions()
    {
        return array_flip($this->getMentionsList());
    }


    /**
     * Count the votes of this tally.
     * Expensive, it's best to memoize the returned value on your end.
     * @return int
     */
    public function countVotes(): int
    {
        $count = 0;
        $tally = $this->getMentionsTally();

        foreach ($this->getMentionsList() as $mention) {
            if (isset($tally[$mention])) {
                $count += (int) $tally[$mention];
            } else {
                trigger_error("Mention `$mention' is not available in the tally.", E_ERROR);
            }
        }

        return $count;
    }


    /**
     * Remove a vote for the provided $mention.
     * Every politician's wet dream that banksters made a reality.
     *
     * May be used during tallying, on copies of tallies,
     * to help with options of similar mentions.
     * @param $mention
     * @return LimajuPollOptionTally
     */
    public function removeOneVoteForMention($mention): self
    {
        if ($this->mentions_tally[$mention] > 0) {
            $this->mentions_tally[$mention] -= 1;
        }

        return $this;
    }


    /**
     * Add $count votes for the provided $mention.
     *
     * @param int $count
     * @param $mention
     * @return LimajuPollOptionTally
     */
    public function addVotesForMention(int $count, $mention): self
    {
        if (isset($this->mentions_tally[$mention])) {
            $this->mentions_tally[$mention] += $count;
        }

        return $this;
    }
}