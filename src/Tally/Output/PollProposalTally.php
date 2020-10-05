<?php


namespace App\Tally\Output;


use App\Entity\Poll;
use Ramsey\Uuid\UuidInterface;


class PollProposalTally
{
    /**
     * @var UuidInterface
     * UUID of the PollProposal that this tally belongs to.
     */
    public $poll_proposal_id;

    /**
     * @var string
     * Final mention tallied, for example the median mention in the standard tally.
     * One of Poll::MENTION_XXX
     */
    public $mention;

    /**
     * @var integer
     * The position of this proposal in its poll, after tallying.
     * The position starts at 1 and ends at <NUMBER_OF_CANDIDATES>.
     * Two proposals MAY have the same position, in extreme cases.
     */
    public $position;

    /**
     * @var array
     * Poll::MENTION_XXX => integer
     * Count of votes for each mention.
     */
    public $mentions_tally;


    /**
     * @var array|string[]
     * The list of Poll::MENTION_XXX this tally uses.
     * The order matters, and must be from "worse" to "best".
     */
    protected $mentions_list;
    // protected $mentions_tree;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return UuidInterface
     */
    public function getPollProposalId(): UuidInterface
    {
        return $this->poll_proposal_id;
    }

    /**
     * @param UuidInterface $poll_proposal_id
     */
    public function setPollProposalId(UuidInterface $poll_proposal_id): void
    {
        $this->poll_proposal_id = $poll_proposal_id;
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
     * Get the median mention. (one of `Poll::MENTION_XXX`)
     * When the count of votes is even, the lower|worse median is privileged.
     *
     * There are many ways to write such a method.
     * Current code is smelly.
     * Perhaps the code in here should be less candid with its input.
     *
     * @param bool $low In case of an even number of votes,
     *                  should we pick the lower median (default) or the higher median?
     * @return string Mention slug in esperanto (one of `Poll::MENTION_XXX`).
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
                Poll::MENTION_TO_REJECT,
                Poll::MENTION_MEDIOCRE,
                Poll::MENTION_INADEQUATE,
                Poll::MENTION_PASSABLE,
                Poll::MENTION_GOOD,
                Poll::MENTION_VERY_GOOD,
                Poll::MENTION_EXCELLENT,
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
     * Helps when sorting the proposals during tallying.
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
                trigger_error("Mention `$mention' is not available in the tally.", E_USER_ERROR);
            }
        }

        return $count;
    }


    /**
     * Remove a vote for the provided $mention.
     * Every politician's wet dream that banksters made a reality.
     *
     * May be used during tallying, on copies of tallies,
     * to help with proposals of similar mentions.
     * @param $mention
     * @return PollProposalTally
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
     * @return PollProposalTally
     */
    public function addVotesForMention(int $count, $mention): self
    {
        if (isset($this->mentions_tally[$mention])) {
            $this->mentions_tally[$mention] += $count;
        }

        return $this;
    }
}