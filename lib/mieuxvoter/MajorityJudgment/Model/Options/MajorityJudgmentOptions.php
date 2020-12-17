<?php


namespace MieuxVoter\MajorityJudgment\Model\Options;


/**
 * Options for Majority Judgment.
 *
 * Ideas:
 * - [x] Default Grade
 * - [ ] Low|High Median
 * - [ ] Automated liquidity (à la proxyfor.me) → no can do with tallies only
 *
 * Class MajorityJudgmentOptions
 * @package MieuxVoter\MajorityJudgment\Resolver\Options
 */
class MajorityJudgmentOptions
{

    protected $default_grade_index = 0;


    /**
     * @return int
     */
    public function getDefaultGradeIndex(): int
    {
        return $this->default_grade_index;
    }

    /**
     * @param int $default_grade_index
     */
    public function setDefaultGradeIndex(int $default_grade_index): void
    {
        $this->default_grade_index = $default_grade_index;
    }

}