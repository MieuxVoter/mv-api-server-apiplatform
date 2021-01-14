<?php


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

}