<?php

namespace Features;

/**
 * Useful steps during development.
 * Most of the time these steps do not show up in the features once they're stable.
 */
class ToolFeatureContext extends BaseFeatureContext
{

    /**
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime) la requ[êe]te$/ui
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) request$/ui
     */
    public function actorPrintsTheRequest($actor)
    {
        $this->actor($actor)->printRequest();
    }


    /**
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime|montre) la r[ée]ponse$/ui
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) response$/ui
     */
    public function actorPrintsTheResponse($actor)
    {
        $this->actor($actor)->printResponse();
    }


    /**
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime|montre) la transaction$/ui
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) transaction$/ui
     */
    public function actorPrintsTheTransaction($actor)
    {
        $this->actor($actor)->printTransaction();
    }


    /**
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime|montre) les (?P<count>.+) dernières transactions$/ui
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) last (?P<count>.+) transactions$/ui
     */
    public function actorPrintsThatManyLastTransactions($actor, $count)
    {
        $this->actor($actor)->printLastTransactions($this->number($count));
    }

//    /**
//     * @When /^I(?P<try> try to)? do a thing like so:?$/iu
//     * /!. The ? quantifier is NOT fully supported on named parenthesis by Behat ATM.
//     *     Use a pipe | instead, like in the doThingsOk step below.
//     */
//    public function doThingKo($try, $pystring) {}
    /**
     * I exist to show how to make an optional regex parameter.  Don't use `?`, use `|`.
     * @When /^I(?P<try>| try to) do a thing like so:?$/iu
     */
    public function doThingOk($try, $pystring) {}
}
