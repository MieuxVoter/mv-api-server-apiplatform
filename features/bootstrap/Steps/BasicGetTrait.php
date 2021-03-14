<?php


namespace Features\Steps;


trait BasicGetTrait
{
    use ActorApi;

    /**
     * @When /^(?:que )?(?P<actor>.+?)(?P<try> tente de|) télécharger? le fichier (?P<filepath>[^ ]+)/ui
     * @When /^(?P<actor>.+?)(?P<try> tries to|) downloads? the file (?P<filepath>[^ ]+)/ui
     */
    public function actorGetsFile($actor, $try, $filepath)
    {
        $actor = $this->actor($actor);
        $actor->api('GET', $filepath, null, [], !empty($try));
    }
}