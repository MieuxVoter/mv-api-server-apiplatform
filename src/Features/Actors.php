<?php


namespace App\Features;


/**
 * A bag of Actors in our feature suite.
 *
 * With this class as an injected dependency of your FeatureContexts, it will be shared between them.
 * We *know* that shared contexts are stinky.  So are files of more than a few hundred lines of code.
 * Do share your suggestions and insights :)
 *
 * Most of the time there will be only one Actor per Scenario: 'I'.
 * But sometimes it is necessary or handy to use multiple Actors.
 *
 * Each Actor gets its own:
 * - [x] API client
 * - [x] transaction log
 * - [ ] response data object
 * - [?] response crawler
 * - …
 *
 * Really not sure we're going to use the "current actor" business. Hence, it's there but commented out.
 *
 * Class Actors
 * @package App\Features
 */
class Actors
{
    /**
     * The keys of this associative array are the actor names (string) as defined the gherkin steps.
     * Question: should we slugify the actor names, so as to be case insensitive and mistake tolerant?
     *
     * @var Actor[]
     */
    protected $actors = [];


    /**
     * @param string $actorName
     * @return Actor
     * @throws ActorConfusion
     */
    public function getActor(string $actorName): Actor
    {
        if ( ! $this->hasActor($actorName)) {
            throw new ActorConfusion(sprintf("The Feature Actor named '%s' was not found.", $actorName));
        }

        return $this->actors[$actorName];
    }


    /**
     * @param string $actorName
     * @return bool
     */
    public function hasActor(string $actorName): bool
    {
        return array_key_exists($actorName, $this->actors);
    }


    /**
     * @param string $actorName
     * @param Actor $actor
     * @return Actors
     */
    public function addActor(string $actorName, Actor $actor): self
    {
        $this->actors[$actorName] = $actor;

        return $this;
    }


    /**
     * @param string $actorName
     * @param string $aliasName
     * @return Actors
     * @throws ActorConfusion
     */
    public function addActorAlias(string $actorName, string $aliasName): self
    {
        if ( ! $this->hasActor($actorName)) {
            throw new ActorConfusion(sprintf("The source Feature Actor '%s' was not found.", $actorName));
        }

        if ($this->hasActor($aliasName)) {
            throw new ActorConfusion(sprintf("The alias Feature Actor '%s' already exists!", $actorName));
        }

        $this->addActor($aliasName, $this->getActor($actorName));

        return $this;
    }

}