<?php

namespace Features\Steps;

use Symfony\Component\DomCrawler\Crawler;


/**
 * This approach is interesting …  Would be a nice fit for a lib.
 * Let's inspect how behatch does it before going further with the trait pattern.
 *
 * Trait DomCrawlerSteps
 * @package Features\Steps
 */
trait DomCrawlerSteps
{

    use ActorApi;  // allows code completion and early failures
//    use YamlApi;
//    use AssertApi;

    /**
     * @When /^(?:que )?(?P<actor>.+?) devr(?:ai(?:t|s|ent)|ions) (?:obtenir|re[çc]evoir) un SVG (?:validant|ayant) *:?/ui
     * @When /^(?P<actor>.+?) should obtain an SVG validating *:?/ui
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
                    $actual_amount = $found->count();

                    if ($constraint['amount'] !== $actual_amount) {
                        $this->actor($actor)->printTransaction();
                    }
                    $this->assertEquals(
                        $constraint['amount'],
                        $actual_amount,
                        "Incorrect amount of ".$constraint['selector']
                    );

                }
            }
        }
    }

}
