<?php


/**
 * Definitions of steps that do nothing, but exist to make the scenarios more idiomatic, easier to understand.
 */
class PoetryFeatureContext extends BaseFeatureContext
{

    /**
     * @Then …
     * @Then ...
     * @Then so
     * @Then puis
     * @Then ensuite
     */
    public function andThenAnotherLine() {}


    /**
     * @When /^(?P<actor>.+?) change d'avis$/u
     * @When /^(?P<actor>.+?) changes? (?:his|her|their|our) minds?$/u
     */
    public function whenActorChangesTheirMind() {}


    /**
     * @When /^(?P<actor>.+?) ne donne pas son avis sur (?:.+)$/ui
     */
    public function whenActorDoesNotDecideOnThings() {}


    /**
     * @When /^(?P<actor>.+?) n['e] ?(?:est|sont) pas invitée?s?$/iu
     */
    public function whenActorIsNotInvited() {}


    /**
     * @When /^(?P<actor>.+?) n'aime pas ça$/ui
     * @When /^(?P<actor>.+?) does not like it$/ui
     */
    public function whenActorDoesNotLikeIt() {}

}
