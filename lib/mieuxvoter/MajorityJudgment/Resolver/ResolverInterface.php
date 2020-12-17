<?php


namespace MieuxVoter\MajorityJudgment\Resolver;


use MieuxVoter\MajorityJudgment\Result\PollResultInterface;
use MieuxVoter\MajorityJudgment\Tally\PollTallyInterface;


/**
 * Takes in a PollTally and resolves it into a PollResult.
 * This is the heart of the algorithm.
 *
 * You may implement your own Resolver, or use one of the provided:
 * - MajorityJudgmentResolver
 * - …
 *
 * Interface ResolverInterface
 * @package MieuxVoter\MajorityJudgment\Resolver
 */
interface ResolverInterface
{
    /**
     * Class name of the Options class to use with this Resolver.
     * Usually specifies what the default Grade is, that kind of thing.
     *
     * The returned class MUST validate `class_exists()`.
     * Probably best to use the `MayAwesomeOptions::class` syntax in here.
     * This enables each Resolver to have their own custom set of options.
     * If your resolver has no options,
     * return `\MieuxVoter\MajorityJudgment\Resolver\Options\NoOptions::class`.
     *
     * @return string
     */
    public function getOptionsClass() : string;

    /**
     * For a given Poll; this computes a Result and returns it
     * This is the heart of the Ranking, where the business logic resides.
     *
     * @param PollTallyInterface $pollTally
     * @param mixed $options An instance of the class provided by `getOptionsClass()`.
     * @return PollResultInterface
     */
    public function resolve(PollTallyInterface $pollTally, $options) : PollResultInterface;
}