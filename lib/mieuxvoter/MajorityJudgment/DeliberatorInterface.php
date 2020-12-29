<?php


namespace MieuxVoter\MajorityJudgment;


use MieuxVoter\MajorityJudgment\Model\Result\PollResultInterface;
use MieuxVoter\MajorityJudgment\Model\Tally\PollTallyInterface;


/**
 * Takes in a PollTally and deliberates it into a PollResult.
 * This is the heart of the algorithm.
 *
 * You may implement your own Deliberator, or use one of the provided:
 * - MajorityJudgmentDeliberator
 * - …
 *
 * Interface DeliberatorInterface
 * @package MieuxVoter\MajorityJudgment
 */
interface DeliberatorInterface
{
    /**
     * Class name of the Options class to use with this Deliberator.
     * Usually specifies what the default Grade is, that kind of thing.
     *
     * The returned class MUST validate `class_exists()`.
     * Probably best to use the `MayAwesomeOptions::class` syntax in here.
     * This enables each Deliberator to have their own custom set of options.
     * If your deliberator has no options, use:
     *     return \MieuxVoter\MajorityJudgment\Model\Options\NoOptions::class;
     *
     * @return string
     */
    public function getOptionsClass() : string;

    /**
     * For a given Poll Tally, this computes a Result and returns it.
     * This is the heart of the Deliberator, where the business logic resides.
     *
     * @param PollTallyInterface $pollTally
     * @param mixed $options An instance of the class provided by `getOptionsClass()`.
     * @return PollResultInterface
     */
    public function deliberate(PollTallyInterface $pollTally, $options) : PollResultInterface;
}