<?php


namespace App\Swagger\Documenter;


use App\Swagger\Documenter\Ability\TranslatorAbility;
use App\Swagger\DocumenterInterface;


/**
 * Adds (generic) descriptions and examples to {id} and {uuid} paths parameters,
 * if none was found.
 *
 * @noinspection PhpUnused
 */
class PathIdDescriptionDocumenter implements DocumenterInterface
{

    use TranslatorAbility;

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
        foreach ($docs['paths'] as $path => $pathDoc) {
            foreach ($docs['paths'][$path] as $method => $routeDoc) {
                if (isset($docs['paths'][$path][$method]['parameters'])) {
                    foreach ($docs['paths'][$path][$method]['parameters'] as $parameter => $parameterDoc) {
                        if ( ! isset($docs['paths'][$path][$method]['parameters'][$parameter]['name'])) {
                            continue;
                        }
                        if (in_array($docs['paths'][$path][$method]['parameters'][$parameter]['name'], ['id', 'uuid'])) {
                            if ( ! isset($docs['paths'][$path][$method]['parameters'][$parameter]['description'])) {
                                $docs['paths'][$path][$method]['parameters'][$parameter]['description'] = $this->trans("oas.parameter.uuid.description");
                            }
                            if ( ! isset($docs['paths'][$path][$method]['parameters'][$parameter]['example'])) {
                                $docs['paths'][$path][$method]['parameters'][$parameter]['example'] = "d434a72c-20cb-480f-9955-1fa2ce2e91b1";
                            }
                        }
                    }
                }
            }
        }

        return $docs;
    }

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
    public function getOrder(): int
    {
        return self::ORDER_VERY_LAST;
    }
}