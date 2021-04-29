<?php

declare(strict_types=1);

namespace App\Swagger;


/**
 * All services implementing this will automatically be used by the SwaggerDecorator.
 * See config/services.yaml
 *
 * Interface DocumenterInterface
 * @package App\Swagger
 */
interface DocumenterInterface
{

    const ORDER_VERY_FIRST = -1024;
    const ORDER_FIRST = -32;
    const ORDER_DEFAULT = 0;
    const ORDER_LAST = 32;
    const ORDER_VERY_LAST = 1024;

    const ORDER_BEFORE = -1;
    const ORDER_AFTER = 1;

    /**
     * Adds custom data to the $docs and returns them.
     *
     * The $context helps knowing whether we're in OASv2 or OASv3.
     *
     * $format is "json"
     * $context is [ "spec_version" => 2, "api_gateway" => false ]
     *
     * @param $docs
     * @param $object
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function document($docs, $object, string $format = null, array $context = []) : array;


    /**
     * Documenters are applied in increasing order.
     * Negative values are allowed.  The default value should be 0.
     * You may use the ORDER_XXX constants for this, if you wish.
     * When two or more documenters have the same order,
     * they are applied in the lexicographical order of their class name,
     * since that is how Symfony DIC seems to load tagged services.
     *
     * @return int
     */
    public function getOrder() : int;
}