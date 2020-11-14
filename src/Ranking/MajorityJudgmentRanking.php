<?php


namespace App\Ranking;


use App\Entity\Poll;
use App\Entity\Poll\Result as PollResult;
use App\Ranking\Options\MajorityJudgmentOptions;


/**
 * Standard Majority Judgment, as described by Balinski & Laraki (2002)
 *
 * Class MajorityJudgmentRanking
 * @package App\Ranking
 */
class MajorityJudgmentRanking implements RankingInterface
{

    /**
     * The name of the Ranking, as used in the API when choosing the ranking algorithm.
     * Each ranking must have a unique name.
     * If two rankings ever share a name, ??? (we'll see later; we'll probably throw, for safety)
     *
     * @return string
     */
    public function getName(): string
    {
        return "Majority Judgment";
    }

    /**
     * The returned class MUST validate `class_exists()`.
     * Probably best to use the `MayAwesomeOptions::class` syntax in here.
     * This allows each Ranking to have their own custom set of options.
     * If your ranking has no options, return `\App\Ranking\Options\NoOptions::class`.
     *
     * @return string
     */
    public function getOptionsClass(): string
    {
        return MajorityJudgmentOptions::class;
    }

    /**
     * For a given Poll; this computes a Result and returns it
     * This is the heart of the Ranking, where the business logic resides.
     *
     * @param Poll $poll
     * @param mixed $options An instance of the class provided by `getOptionsClass()`.
     * @return PollResult
     */
    public function resolve(Poll $poll, $options): PollResult
    {
        $leaderboard = [];


        // TODO: Implement resolve() method.


        $pollResult = new PollResult();
        $pollResult->setPoll($poll);
        $pollResult->setAlgorithm($this->getName());
        $pollResult->setLeaderboard($leaderboard);

        return $pollResult;
    }
}