<?php


namespace Features\Steps;


use App\Features\Actor;


trait ActorApi
{
    abstract protected function actor($actorName, $createIfNone=false): Actor;
}