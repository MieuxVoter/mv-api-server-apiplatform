<?php


namespace Features\Steps;


use App\Features\Actor;


/**
 * Allows code completion and early failures.
 *
 * Trait ActorApi
 * @package Features\Steps
 */
trait ActorApi
{
    abstract protected function actor($actorName, $createIfNone=false): Actor;
}