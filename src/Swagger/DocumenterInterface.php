<?php


namespace App\Swagger;


/**
 * All services implementing this will be used by the SwaggerDecorator.
 *
 * Interface DocumenterInterface
 * @package App\Swagger
 */
interface DocumenterInterface
{

    /**
     * Adds custom data to the $docs and returns them.
     *
     * @param $docs
     * @param $object
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function document($docs, $object, string $format = null, array $context = []) : array;

}