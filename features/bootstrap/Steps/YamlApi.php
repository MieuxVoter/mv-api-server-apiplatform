<?php


namespace Features\Steps;


use App\Features\Actor;


/**
 * Allows code completion and early failures.
 *
 * Trait YamlApi
 * @package Features\Steps
 */
trait YamlApi
{
    abstract protected function yaml(string $pystring);
}