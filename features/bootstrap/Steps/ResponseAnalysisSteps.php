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


    /**
     * @Then /^(?P<actor>.+?) devr(?:ai[st]|aient|ions)(?: encore| aussi)? (?:(?P<ok>réussir)|(?P<ko>échouer))$/u
     * @Then /^(?P<actor>.+?) should (?:(?P<ok>succeed)|(?P<ko>fail))$/
     */
    public function actorShouldSucceedOrFail($actor, $ok=null, $ko=null)
    {
        $actor = $this->actor($actor);
        $tx = $actor->getLastTransaction();

        if (empty($ko)) {
            $actor->assertTransactionSuccess($tx);
        } else if (empty($ok)) {
            $actor->assertTransactionFailure($tx);
        } else {
            $this->fail("Bad Step Regex?");
        }
    }


    /**
     * @Then /^(?P<actor>.+?) devr(?:ai[st]|aient|ions)(?: encore| aussi)? (?:obtenir|re[cç]evoir) (?:un|le) code(?: http)? (?P<code>[0-9]+)$/ui
     */
    public function actorShouldReceiveHttpCode($actor, $code)
    {
        $actor = $this->actor($actor);
        $expected = (int) $code;
        $actual = $actor->getLastTransaction()->getResponse()->getStatusCode();
        if ($expected != $actual) {
            $actor->printTransaction();
            $this->fail("Expected HTTP response code $expected but got $actual instead.");
        }
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