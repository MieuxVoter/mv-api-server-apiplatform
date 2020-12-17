<?php


namespace MieuxVoter\MajorityJudgment\Resolver;


use App\Entity\Poll\Proposal\Result;
use MieuxVoter\MajorityJudgment\Resolver\Options\MajorityJudgmentOptions;
use MieuxVoter\MajorityJudgment\Result\GenericPollResult;
use MieuxVoter\MajorityJudgment\Result\PollResultInterface;
use MieuxVoter\MajorityJudgment\Tally\PollTallyInterface;
use MieuxVoter\MajorityJudgment\Tally\ProposalTallyInterface;


/**
 *
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

        // TODO: Implement resolve() method.




        $result = new GenericPollResult($ranked_proposals);

        return $result;
    }

    static function computeProposalResultWithoutRank(
        ProposalTallyInterface $proposalTally
    ) : Result
    {

    }
}