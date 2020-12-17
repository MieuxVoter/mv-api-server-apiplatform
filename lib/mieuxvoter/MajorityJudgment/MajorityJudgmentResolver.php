<?php


namespace MieuxVoter\MajorityJudgment;

use MieuxVoter\MajorityJudgment\Model\Options\MajorityJudgmentOptions;
use MieuxVoter\MajorityJudgment\Model\Result\GenericPollResult;
use MieuxVoter\MajorityJudgment\Model\Result\PollResultInterface;
use MieuxVoter\MajorityJudgment\Model\Result\RankedProposal;
use MieuxVoter\MajorityJudgment\Model\Tally\PollTallyInterface;
use MieuxVoter\MajorityJudgment\Model\Tally\ProposalTallyInterface;


/**
 * Score-based MJ resolver.
 *
 * https://scholar.google.fr/scholar?q=majority+judgment
 *
 * Ideally, since our algorithm is in theory parallelizable,
 * we should support parallel https://www.php.net/manual/fr/intro.parallel.php
 *
 * This would enable us to get support for huge amounts of proposals.
 *
 * Class MajorityJudgmentResolver
 * @package MieuxVoter\MajorityJudgment\Resolver
 */
class MajorityJudgmentResolver implements ResolverInterface
{

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
    public function resolve(PollTallyInterface $pollTally, $options): PollResultInterface
    {
        $ranked_proposals = [];

        // I. Compute the score of each proposal
        foreach ($pollTally->getProposalsTallies() as $proposalsTally) {
            $unranked_proposal = self::computeUnrankedProposal(
                $proposalsTally,
                $pollTally->getParticipantsAmount(),
                $options
            );
            $ranked_proposals[] = $unranked_proposal;
        }

        // II. Sort the proposals using their score (higher is "better")
        $sortSuccess = usort(
            $ranked_proposals,
            function(RankedProposal $rpa, RankedProposal $rpb)
            {
                return strcmp($rpb->getScore(), $rpa->getScore());
            }
        );
        assert($sortSuccess, "Sorting by score must work!");

        // III. Compute the rank of each proposal
        $rank = 1;  // human-centric value, so starts at 1 ("best" proposal)
        $amountOfProposals = count($ranked_proposals);
        for ($i = 0 ; $i < $amountOfProposals ; $i++) {

            if ($i == 0) {
                $ranked_proposals[$i]->setRank($rank);
            } else {
                if (
                    $ranked_proposals[$i]->getScore()
                    ==
                    $ranked_proposals[$i-1]->getScore()
                ) {
                    // Wow, we have a *perfect* ex-æquo!
                    $ranked_proposals[$i]->setRank(
                        $ranked_proposals[$i-1]->getRank()
                    );
                } else {
                    $ranked_proposals[$i]->setRank($rank);
                }
            }

            $rank++;
        }

        // IV. We've got everything we need, time to build the Result
        $result = new GenericPollResult($ranked_proposals);

        return $result;
    }

    /**
     * Computes the score of the provided proposal.
     * Does not compute the rank ; this will be done by resolve().
     * Static method for (later) easier parallelization.
     *
     * @param ProposalTallyInterface $proposalTally
     * @param int $participantsAmount
     * @param MajorityJudgmentOptions $options
     * @return RankedProposal
     */
    static function computeUnrankedProposal(
        ProposalTallyInterface $proposalTally,
        int $participantsAmount,
        MajorityJudgmentOptions $options
    ) : RankedProposal
    {
        $unrankedProposal = new RankedProposal();

        $unrankedProposal->setProposal($proposalTally->getProposal());

        // FIXME: compute score
        $gradesTallies = $proposalTally->getGradesTallies();

        $grades = [];  // "worst" to "best"
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
            $grades[] = $gradeTally->getGrade();
        }
        $amountOfGrades = count($grades);

        assert(
            $options->getDefaultGradeIndex() < $amountOfGrades,
            "Default grade is within range."
        );
        $defaultGrade = $grades[$options->getDefaultGradeIndex()];

        foreach ($gradesTallies as $gradeTally) {
            $gradeTally->getTally();
            // FIXME: resume coding here
        }


        return $unrankedProposal;
    }
}