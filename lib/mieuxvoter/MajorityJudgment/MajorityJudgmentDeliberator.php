<?php


namespace MieuxVoter\MajorityJudgment;


use MieuxVoter\MajorityJudgment\Model\Options\MajorityJudgmentOptions;
use MieuxVoter\MajorityJudgment\Model\Result\GenericPollResult;
use MieuxVoter\MajorityJudgment\Model\Result\PollResultInterface;
use MieuxVoter\MajorityJudgment\Model\Result\ProposalResult;
use MieuxVoter\MajorityJudgment\Model\Tally\PollTallyInterface;
use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTallyInterface;


/**
 * Score-based Majority Judgment deliberator.
 *
 * TODO: add links to relevant papers and perhaps wikipedia page
 * https://scholar.google.fr/scholar?q=majority+judgment
 *
 * Ideally, since this algorithm is parallelizable per proposal,
 * we could support `parallel` if the need arises.
 * See https://www.php.net/manual/fr/intro.parallel.php
 * This would enable us to get support for huge amounts of proposals.
 *
 * Tests: Tests/MajorityJudgmentDeliberatorTest.php
 *
 * Class MajorityJudgmentDeliberator
 * @package MieuxVoter\MajorityJudgment
 */
class MajorityJudgmentDeliberator implements DeliberatorInterface
{
    // These could be derived from the data instead of being set arbitrarily like this
    const GRADES_AMOUNT_MAX_DIGITS = 3; // 10e3 = 1000 grades should be more than enough
    const PARTICIPANTS_AMOUNT_MAX_DIGITS = 11; // 10e11 = 10 times the humans on Earth in 2020


    /**
     * Class name of the Options class to use with this Resolver.
     * Usually specifies what the default Grade is, that kind of thing.
     *
     * The returned class MUST validate `class_exists()`.
     * Probably best to use the `MayAwesomeOptions::class` syntax in here.
     * This enables each Resolver to have their own custom set of options.
     * If your resolver has no options, use
     * return \MieuxVoter\MajorityJudgment\Resolver\Options\NoOptions::class;
     *
     * @return string
     */
    public function getOptionsClass(): string
    {
        return MajorityJudgmentOptions::class;
    }

    /**
     * For a given Poll Tally, this computes a Result and returns it.
     * This is the heart of the Ranking, where the business logic resides.
     *
     * @param PollTallyInterface $pollTally
     * @param mixed $options An instance of the class provided by `getOptionsClass()`.
     * @return PollResultInterface
     */
    public function deliberate(PollTallyInterface $pollTally, $options): PollResultInterface
    {
        $proposalResults = [];

        // I. Compute the score of each proposal, skip the rank for now
        foreach ($pollTally->getProposalsTallies() as $proposalsTally) {
            $scoredProposal = self::computeUnproposalResult(
                $proposalsTally,
                $pollTally->getParticipantsAmount(),
                $options
            );
            $proposalResults[] = $scoredProposal;
        }

        // II. Sort the proposals using their score (higher is "better")
        $sortSuccess = usort(
            $proposalResults,
            function(ProposalResult $rpa, ProposalResult $rpb)
            {
                return strcmp($rpb->getScore(), $rpa->getScore());
            }
        );
        assert($sortSuccess, "Sorting by score must succeed!");

        // III. Compute the rank of each proposal
        $rank = 1;  // human-centric value, so starts at 1 ("best" proposal)
        $amountOfProposals = count($proposalResults);
        for ($i = 0 ; $i < $amountOfProposals ; $i++) {

            if ($i == 0) {
                $proposalResults[$i]->setRank($rank);
            } else {
                if (
                    $proposalResults[$i]->getScore()
                    ==
                    $proposalResults[$i-1]->getScore()
                ) {
                    // Wow, we have a *perfect* ex-æquo!
                    $proposalResults[$i]->setRank(
                        $proposalResults[$i-1]->getRank()
                    );
                } else {
                    $proposalResults[$i]->setRank($rank);
                }
            }

            $rank++;
        }

        // IV. We've got everything we need, time to build the Result
        $result = new GenericPollResult($proposalResults);

        return $result;
    }


    /**
     * Computes the score of the provided proposal.
     * Does not compute the rank ; this will be done by deliberate().
     * Static (context-free) method for (later) easier parallelization.
     *
     * @param ProposalTallyInterface $proposalTally
     * @param int $participantsAmount
     * @param MajorityJudgmentOptions $options
     * @return ProposalResult
     */
    static function computeUnproposalResult( // computeProposalResultWithScoreOnly?
        ProposalTallyInterface $proposalTally,
        int $participantsAmount,
        MajorityJudgmentOptions $options
    ) : ProposalResult
    {
        $unproposalResult = new ProposalResult();
        $unproposalResult->setProposal($proposalTally->getProposal());

        // I. Collect data and check its sanity
        $gradesTallies = $proposalTally->getGradesTallies();
        $grades = [];  // "worst" to "best"
        $tallies = [];  // same order as grades, is mutated by algorithm
        $actualParticipantsAmount = 0;
        foreach ($gradesTallies as $gradeTally) {
            assert(
                $gradeTally->getProposal() == $proposalTally->getProposal(),
                "Proposals must match."
            );
            $grade = $gradeTally->getGrade();
            assert(
                ! in_array($grade, $grades),
                "Grades must be unique."
            );
            $grades[] = $grade;
            $tally = $gradeTally->getTally();
            assert(
                0 <= $tally
                &&
                $participantsAmount >= $tally,
                "Tally is within meaningful range."
            );
            $tallies[] = $tally;
            $actualParticipantsAmount += $tally;
        }
        $amountOfGrades = count($grades);

        // II. Prepare a default Grade
        $defaultGradeIndex = $options->getDefaultGradeIndex();
        assert(
            0 <= $defaultGradeIndex
            &&
            $amountOfGrades > $defaultGradeIndex,
            "Default grade is within range."
        );
        //$defaultGrade = $grades[$defaultGradeIndex];

        // III. Fill the blanks with the default Grade
        assert(
            $actualParticipantsAmount <= $participantsAmount,
            "The amount of participants is correct."
        );
        if ($actualParticipantsAmount < $participantsAmount) {
            $tallies[$defaultGradeIndex] += $participantsAmount - $actualParticipantsAmount;
        }

        // IV. Compute the median
        $medianGradeIndex = self::getMedianGradeIndex($tallies);
        $median = $grades[$medianGradeIndex];
        $unproposalResult->setMedian($median);

        // V. Compute a lexicographical score (higher is "better")
        $score = "";
        for ($i = 0 ; $i < $amountOfGrades ; $i++) {
            if (0 < $i) {
                $score .= '/';
            }

            $medianGradeIndex = self::getMedianGradeIndex($tallies);
            $score .= sprintf(
                "%0".((string) self::GRADES_AMOUNT_MAX_DIGITS)."d",
                $medianGradeIndex
            );

            // Collect biggest of the two groups of grades outside of the median.
            // Group Grade is the index of the grade in the group that is adjacent to the median group.
            // Group Sign is:
            // - +1 if the group promotes higher grades (adhesion)
            // - -1 if the group promotes lower grades (contestation)
            // - ±0 if there is no spoon (nor group)
            [$groupSize, $groupSign, $groupGrade] = self::getBiggestGroup(
                $medianGradeIndex, $tallies
            );

            $score .= '_';
            // Note: the following caps the supported amount of participants.
            // Could be bumped up by deriving the $amountOfDigits from $participantsAmount.
            $amountOfDigits = self::PARTICIPANTS_AMOUNT_MAX_DIGITS;
            $score .= sprintf(
                "%0".($amountOfDigits+1)."d",
                pow(10, $amountOfDigits) + $groupSign * $groupSize
            );

            self::regradeJudgments($tallies, $medianGradeIndex, $groupGrade);
        }

        //dump("Score", $proposalTally->getProposal(), $score);
        $unproposalResult->setScore($score);

        return $unproposalResult;
    }


    /**
     * Find the index of the median grade from the given array of tallies.
     *
     * This method may be optimized:
     * - pass $total as parameter
     * - median-finding loop may perhaps be optimized as well
     *
     * @param array $tallies
     *   Indexed array of integers.
     *   Tally for each Grade, in the 'worst" grade to "best" grade order.
     *   A Tally here is an amount of Judgments emitted with a specific Grade.
     *   This looks like the merit profile, in other words.
     *   Eg: A value of [1, 4, 3] would mean (Reject=1, Passable=4, Good=3)
     * @param bool $low
     *   Use the low (default) or high median, when there's an EVEN amount of judgments.
     * @return int
     */
    static function getMedianGradeIndex($tallies, $low=true): int
    {
        // We could perhaps pass this $total as parameter,
        // but that would mean we trust that the total is correct, since
        // when we use this method we assume that the all the tallies
        // are already filled with the default values.
        // Since for now we need resilience more than performance, we compute it,
        // but it's wasted CPU cycles ; refactor at will.
        $total = 0;
        foreach ($tallies as $tally) {
            $total += $tally;
        }
        //////////////////////////////

        $adjustedTotal = $total - 1;
        if ( ! $low) {
            $adjustedTotal = $total + 1;
        }

        $medianIndex = intdiv($adjustedTotal, 2);
        $cursorIndex = 0;
        foreach ($tallies as $gradeIndex => $tally) {
            if (0 == $tally) {
                continue;
            }

            $startIndex = $cursorIndex;
            $cursorIndex += $tally;
            $endIndex = $cursorIndex;

            if (
                $startIndex <= $medianIndex
                &&
                $medianIndex < $endIndex
            ) {
                return $gradeIndex;
            }
        }

        return 0;
    }


    /**
     * Gets details about the biggest of the two groups that did not give the median grade.
     * This method works more generally with $aroundGradeIndex, which is set to the median grade in practice,
     * in the current score calculus implementation above.
     *
     * Group Size is the amount of judgments in the group.
     * Group Grade is the index of the grade that is adjacent to the median group.
     * Groups may contain multiple grades, but only the closest to the median group interests us.
     * Group Sign is:
     * - +1 if the group promotes higher grades (adhesion)
     * - -1 if the group promotes lower grades (contestation)
     * - -1 by default (no judgments, or only lowest grade) ← coupled to 'LOW' MEDIAN, innit?
     *
     *
     * THIS ASSUMES A "LOW" MEDIAN ("WORST" grade) IN EVEN SCENARIOS
     *
     *
     * @param $aroundGradeIndex
     * @param array $tallies
     * @return array [groupSize, groupSign, groupGrade]
     */
    static function getBiggestGroup($aroundGradeIndex, array $tallies) : array
    {
        $belowGroupSize = 0;
        $belowGroupSign = -1;
        $belowGroupGrade = 0; // index

        $aboveGroupSize = 0;
        $aboveGroupSign = 1;
        $aboveGroupGrade = 0; // index

        $amountOfGrades = count($tallies);
        for ($gradeIndex = 0; $gradeIndex < $amountOfGrades; $gradeIndex++) {
            if (0 == $tallies[$gradeIndex]) {
                continue;
            }
            if ($gradeIndex < $aroundGradeIndex) {
                $belowGroupSize += $tallies[$gradeIndex];
                $belowGroupGrade = $gradeIndex;
            }
            if ($gradeIndex > $aroundGradeIndex) {
                $aboveGroupSize += $tallies[$gradeIndex];
                if (0 == $aboveGroupGrade) {
                    $aboveGroupGrade = $gradeIndex;
                }
            }
        }

        // /!. Assumption of LOW median with `>` /!.
        if ($aboveGroupSize > $belowGroupSize) {
            return [$aboveGroupSize, $aboveGroupSign, $aboveGroupGrade];
        }
        return [$belowGroupSize, $belowGroupSign, $belowGroupGrade];
    }


    /**
     * Mutate the $tallies to put the judgments $fromGrade into $intoGrade.
     * This is used by the current implementation of the score calculus.
     *
     * I don't like having such a method around (theoretical security concerns),
     * so if we can rewrite the score calculus to not need such a method
     * without introducing other mutating methods, we can and should remove this.
     *
     * @param $tallies
     * @param $fromGrade
     * @param $intoGrade
     */
    static function regradeJudgments(&$tallies, $fromGrade, $intoGrade)
    {
        $amountOfGrades = count($tallies);
        assert(
            $fromGrade >= 0
            &&
            $fromGrade < $amountOfGrades,
            "'From' Grade is within acceptable range."
        );
        assert(
            $intoGrade >= 0
            &&
            $intoGrade < $amountOfGrades,
            "'Into' Grade is within acceptable range."
        );

        if ($fromGrade == $intoGrade) {
            return;
        }

        $tallies[$intoGrade] += $tallies[$fromGrade];
        $tallies[$fromGrade] = 0;
    }

}