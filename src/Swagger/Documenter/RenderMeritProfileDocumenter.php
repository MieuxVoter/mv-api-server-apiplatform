<?php


namespace App\Swagger\Documenter;


use App\Swagger\Documenter\Ability\TranslatorAbility;
use App\Swagger\DocumenterInterface;
use Symfony\Component\HttpFoundation\Response;


/** @noinspection PhpUnused */


class RenderMeritProfileDocumenter implements DocumenterInterface
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
        $version = $context['spec_version'];

        $path = '/render/merit-profile.svg';
        $method = 'get';

        $extraDocumentation = [
            'paths' => [
                $path => [
                    $method => [
                        'tags' => ['Tools'],
                        'operationId' => 'getSvgMeritProfileFromTally',
                        'summary' => $this->trans('oas.merit_profile.summary'),
                        'description' => $this->trans('oas.merit_profile.description'),
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => 'A SVG image.',
                            ],
//                            Response::HTTP_BAD_REQUEST => [
//                                'description' => 'Provided tally or options cannot be parsed.',
//                            ],
                        ],
                    ],
                ],
            ],
        ];

        switch ($version) {
            case 2:
                $extraDocumentation = array_merge_recursive($extraDocumentation, [
                    'paths' => [
                        $path => [
                            $method => [
                                'produces' => [
                                    'text/svg',
                                ],
                                'parameters' => [
                                    [
                                        'name' => 'tally',
                                        'in' => "query",
                                        'description' => $this->trans('oas.merit_profile.parameters.tally.description'),
                                        'example' => $this->trans('oas.merit_profile.parameters.tally.example'),
                                        'required' => true,
                                        'schema' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    [
                                        'name' => 'width',
                                        'in' => "query",
                                        'description' => $this->trans('oas.merit_profile.parameters.width.description'),
                                        'example' => $this->trans('oas.merit_profile.parameters.width.example'),
                                        'required' => false,
                                        'schema' => [
                                            'type' => 'integer',
                                        ],
                                    ],
                                    [
                                        'name' => 'height',
                                        'in' => "query",
                                        'description' => $this->trans('oas.merit_profile.parameters.height.description'),
                                        'example' => $this->trans('oas.merit_profile.parameters.height.example'),
                                        'required' => false,
                                        'schema' => [
                                            'type' => 'integer',
                                        ],
                                    ],
                                ],
//                                'responses' => [
//                                    Response::HTTP_OK => [
//                                        'content' => [
//                                            "application/ld+json" => [
//                                                "schema" => [
//                                                    '$ref' =>  '#/definitions/Token',
//                                                ],
//                                            ],
//                                            "application/json" => [
//                                                "schema" => [
//                                                    '$ref' =>  '#/definitions/Token',
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                ],
                            ],
                        ],
                    ],
                ]);
                break;
            case 3:
            default:
                $extraDocumentation = array_merge_recursive($extraDocumentation, [
                    'paths' => [
                        $path => [
                            $method => [
                                'responses' => [
                                    Response::HTTP_OK => [
//                                        'schema' => [
//                                            '$ref' => '#/components/schemas/Token',
//                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        }

        return array_merge_recursive($docs, $extraDocumentation);
    }

    /**
     * Documenters are applied in increasing order.
     * Negative values are allowed.  The default value should be 0.
     * You may use the ORDER_XXX constants for this, if you wish.
     * When two or more documenters have the same order,
     * they are applied in the lexicographical order of their class name.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return self::ORDER_DEFAULT;
    }
}