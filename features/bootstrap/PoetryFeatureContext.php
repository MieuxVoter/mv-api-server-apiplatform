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
     * @When /^(?P<actor>.+?) changes? (?:his|her|their|our) minds?$/u
     * @When /^(?P<actor>.+?) change d'avis$/u
     */
    public function whenActorChangesTheirMind() {}


    /**
     * @When /^(?P<actor>.+?) ne donne pas son avis sur (?:.+)$/ui
     */
    public function whenActorDoesNotDecideOnThings() {}

}
