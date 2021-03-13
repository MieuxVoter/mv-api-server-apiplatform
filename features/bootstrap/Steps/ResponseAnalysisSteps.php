<?php


namespace Features\Steps;


trait ResponseAnalysisSteps
{
    use ActorApi;
    use YamlApi;
    //use PhpUnitApi;  for fail()

    /**
     * @When /^la réponse à (?P<actor>.+?) devrait comporter *:?/ui
     * @When /^the answer to (?P<actor>.+?) should include *:?/ui
     */
    public function actorResponseShouldInclude($actor, $pystring)
    {
        $actor = $this->actor($actor);
        $yaml = $this->yaml($pystring);

        $this->assertArrayContains($yaml, $actor->getLastTransaction()->getResponseJson());
    }

    public function assertArrayContains($expected, $actual)
    {
        if ( ! is_array($expected)) {
            $this->fail("Expectations must be an array (for now).");
        }
        if ( ! is_array($actual)) {
            $this->fail("Expected an array, but got `$actual'.");
        }
        foreach ($expected as $k => $sub_expected) {
            if ( ! isset($actual[$k])) {
                $this->fail("Key $k was not found.");
            }
            if (is_array($sub_expected)) {
                $this->assertArrayContains($expected[$k], $actual[$k]);
            } else {
                $this->assertEquals($sub_expected, $actual[$k]);
            }
        }

    }
}