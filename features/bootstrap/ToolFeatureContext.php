<?php


use Symfony\Component\DomCrawler\Crawler;


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

    /**
     * @When /^(?:que )?(?P<actor>.+?) devrait obtenir un SVG validant *:?/ui
     */
    public function actorShouldObtainSvg($actor, $pystring)
    {
        $constraints = $this->yaml($pystring);
        $response = $this->actor($actor)->getLastTransaction()->getResponse();

        $svg = new Crawler($response->getContent());

        foreach ($constraints as $constraint) {
            if (isset($constraint['selector'])) {

                if (isset($constraint['amount'])) {

                    $found = $svg->filter($constraint['selector']);
                    $this->assertEquals(
                        $constraint['amount'],
                        $found->count(),
                        "Incorrect amount of ".$constraint['selector']
                    );

                }
            }
        }
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
