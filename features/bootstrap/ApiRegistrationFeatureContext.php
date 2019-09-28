<?php /** @noinspection PhpDocSignatureInspection */


/**
 * Steps for registration, using the REST form of the API
 * because we can't manage to do registration via graphql
 *
 * Do fix this if you can. (not expected to be trivial)
 */
class ApiRegistrationFeatureContext extends BaseFeatureContext
{

    /**
     * @When /^(?P<actor>.+?) registers? as the new citizen "(?P<name>.*)" with AAT "(?P<aat>.*)"$/
     * @When /^(?
P<actor>.+) [mst]'inscri[st] en tant que citoyen(?:⋅?ne)? avec le pseudonyme "(?P<name>.*)" et le (?:JAA|jeton) "(?P<aat>.*)"$/ui
     */
    public function iRegisterPseudonimicallyAsNewCitizenWithAat($actor, $name, $aat)
    {
        print("🐵 Registering as ".$name."…");

        $this->actor($actor, true)->apiOld('POST', '/user', ["name" => $name, "aat" => $aat]);
    }


    /**
     * @When :actor register anonymously as citizen with AAT :aat
     * @When /^(?P<actor>.+) [mst]'inscri[st](?: anonymement)? en tant que citoyen(?:⋅?ne)? avec le (?:JAA|jeton) "(?P<aat>.*)"$/ui
     */
    public function iRegisterAnonymouslyAsNewCitizenWithAat($actor, $aat)
    {
        print("🤖 Registering anonymously…");

        $this->actor($actor, true)->apiOld('POST', '/user', ["aat" => $aat]);
    }

}
