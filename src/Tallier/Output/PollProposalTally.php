<?php


namespace App\Tallier\Output;


use Ramsey\Uuid\UuidInterface;


class PollProposalTally
{
    /**
     * @var UuidInterface
     * UUID of the Proposal that this tally belongs to.
     */
    public $poll_proposal_id;

    /**
     * @var string
     * Final mention tallied, for example the median mention in the standard tally.
     * One of Poll::MENTION_XXX
     */
    public $median_grade;

    /**
     * @var integer
     * The rank of this proposal in its poll, after tallying.
     * The rank starts at 1 and usually ends at <AMOUNT_OF_PROPOSALS>.
     * Two or more proposals MAY have the same rank, in extreme cases.
     */
    public $rank;

    /**
     * @var array
     * grade_name => integer
     * Count of votes for each grade.
     */
    public $grades_tally;


    /**
     * @var array|string[]
     * The list of the names of grades this tally & poll use.
     * The order matters, and MUST be from "worse" to "best".
     */
    protected $grades_names;
    // protected $grades_tree;

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
    public function getMedianGrade(): string
    {
        return $this->median_grade;
    }

    /**
     * @param string $median_grade
     */
    public function setMedianGrade(string $median_grade): void
    {
        $this->median_grade = $median_grade;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    /**
     * @return array
     */
    public function getGradesTally(): array
    {
        return $this->grades_tally;
    }

    /**
     * @param array $grades_tally
     */
    public function setGradesTally(array $grades_tally): void
    {
        $this->grades_tally = $grades_tally;
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
        $mentions = $this->getGradesNames();
//        $order = $this->getMentionsPositions();
        $tally = $this->getGradesTally();
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
    public function getGradesNames()
    {
        if (null === $this->grades_names) {
            trigger_error("No grades list.");
        }

        return $this->grades_names;
    }


    /**
     * @param array|string[] $grades_names
     */
    public function setGradesNames($grades_names): void
    {
        $this->grades_names = $grades_names;
    }


    /**
     * Yields the mapping of the mentions to their "worth", an integer between 0 and `mentionsCount-1`.
     * Helps when sorting the proposals during tallying.
     * @deprecated
     * @return array of MENTION_XXX => N
     */
    public function getMentionsPositions()
    {
        return array_flip($this->getGradesNames());
    }


    /**
     * Count the votes of this tally.
     * Expensive, it's best to memoize the returned value on your end.
     * @return int
     */
    public function countVotes(): int
    {
        $count = 0;
        $tally = $this->getGradesTally();

        foreach ($this->getGradesNames() as $mention) {
            if ( ! isset($tally[$mention])) {
                trigger_error("Mention `$mention' is not available in the tally.", E_USER_ERROR);
            }
            $count += (int) $tally[$mention];
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
        if ($this->grades_tally[$mention] > 0) {
            $this->grades_tally[$mention] -= 1;
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
    public function addVotesForGrade(int $count, $mention): self
    {
        if (isset($this->grades_tally[$mention])) {
            $this->grades_tally[$mention] += $count;
        }

        return $this;
    }
}