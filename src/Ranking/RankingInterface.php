<?php


namespace App\Tallier;


use App\Entity\Poll;
use App\Entity\Poll\Result as PollResult;


/**
 * The purpose of a Ranking is to create a Poll\Result from the Ballots (and perhaps Delegations) of a Poll.
 *
 * Since there is a galaxy of possibilities when ranking proposals, you may implement your own Ranking.
 * It will need to implement this interface to be automatically available to the API.
 * It will be auto-wired and auto-configured as a Service tagged `poll_ranking`,
 * and you may inject any other Service in the constructor or `@required` setters.
 */
interface RankingInterface
{
    /**
     * The name of the Ranking, as used in the API when choosing the ranking algorithm.
     * Each ranking must have a unique name.
     * If two rankings ever share a name, ??? (we'll see later; we'll probably throw, for safety)
     *
     * @return string
     */
    public function getName() : string;

    /**
     * For a given Poll; this computes a Result and returns it
     * This is the heart of the Ranking, where the business logic resides.
     *
     * @param Poll $poll
     * @param mixed $options An instance of the class provided by `getOptionsClass()`.
     * @return PollResult
     */
    public function resolve(Poll $poll, $options) : PollResult;

    // a way to pass options to the ranking
//    public function setOptions(array $options) : void;
    // perhaps a getter returning a FormType or Form instead, or blueprints to create one?
    // …
    // perhaps something like this:

    /**
     * The returned class MUST validate `class_exists()`.
     * Probably best to use the `MayAwesomeOptions::class` syntax in here.
     * This allows each Ranking to have their own custom set of options.
     * If your ranking has no options, return `\App\Ranking\Options\None::class`.
     *
     * @return string
     */
    public function getOptionsClass() : string;
}