<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Useful steps during development.
 * Most of the time these steps do not show up in the features once they're stable.
 */
class ToolFeatureContext extends BaseFeatureContext
{

    /**
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) request$/ui
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime) la requ[êe]te$/ui
     */
    public function actorPrintsTheRequest($actor)
    {
        $this->actor($actor)->printRequest();
    }


    /**
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) response$/ui
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime) la r[ée]ponse$/ui
     */
    public function actorPrintsTheResponse($actor)
    {
        $this->actor($actor)->printResponse();
    }


    /**
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) transaction$/ui
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime) la transaction$/ui
     */
    public function actorPrintsTheTransaction($actor)
    {
        $this->actor($actor)->printTransaction();
    }


    /**
     * @When /^(?P<actor>.+?) +(?:print|dump)s? th(?:e|at|is) last (?P<count>.+) transactions$/ui
     * @When /^(?:que )?(?P<actor>.+?) *(?:affiche|imprime) les (?P<count>.+) dernières transactions$/ui
     */
    public function actorPrintsThatManyLastTransactions($actor, $count)
    {
        $this->actor($actor)->printLastTransactions($this->number($count));
    }


//    /**
//     * /!. The ? quantifier is NOT fully supported on named parenthesis by Behat ATM.
//     *     Use a pipe | instead, like in the doThingsOk step below.
//     * @When /^I(?P<try> try to)? do a thing like so:?$/iu
//     */
//    public function doThingKo($try, $pystring) {}
    /**
     * @When /^I(?P<try> try to|) do a thing like so:?$/iu
     */
    public function doThingOk($try, $pystring) {}
}
