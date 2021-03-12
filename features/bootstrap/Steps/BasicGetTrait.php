<?php


namespace Features\Steps;


trait BasicGetTrait
{
    use ActorApi;

    /**
     * @When /^(?:que )?(?P<actor>.+?) télécharge le fichier (?P<filepath>[^ ]+)/ui
     * @When /^(?P<actor>.+?) downloads the file (?P<filepath>[^ ]+)/ui
     */
    public function actorGetsFile($actor, $filepath)
    {
        $actor = $this->actor($actor);
        $actor->api('GET', $filepath);
    }
}