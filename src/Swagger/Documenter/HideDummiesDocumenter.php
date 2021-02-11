<?php


namespace App\Swagger\Documenter;


use App\Swagger\DocumenterInterface;


/** @noinspection PhpUnused */


/**
 * Some endpoints need to exist for ApiPlatform to function.
 * They do not effect anything (by design), so we hide them.
 *
 * Class HideDummiesDocumenter
 * @package App\Swagger\Documenter
 */
class HideDummiesDocumenter implements DocumenterInterface
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
    public function document($docs, $object, string $format = null, array $context = []): array
    {
        unset($docs['paths']['/proposal_results/{id}']);
        unset($docs['paths']['/proposal_grade_results/{id}']);

        return $docs;
    }

    /**
     * Documenters are applied in increasing order.
     * Negative values are allowed.  The default value should be 0.
     * When two or more documenters have the same order,
     * they are applied in the lexicographical order of their class name/.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return self::ORDER_VERY_FIRST;
    }
}