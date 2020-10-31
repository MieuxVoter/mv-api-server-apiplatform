<?php

declare(strict_types=1);

namespace App\Swagger;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class JwtDecorator implements NormalizerInterface
{
    private NormalizerInterface $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        $docs['components']['schemas']['Token'] = [
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ];

        $docs['components']['schemas']['Credentials'] = [
            'type' => 'object',
            'properties' => [
                'usernameOrEmail' => [
                    'type' => 'string',
                    'example' => 'api',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'api',
                ],
            ],
        ];

        $tokenDocumentation = [
            'paths' => [
                '/_jwt' => [
                    'post' => [
                        'tags' => ['Token'],
                        'operationId' => 'postCredentialsItem',
                        'summary' => 'Gets a JWT token to login',
                        'requestBody' => [
                            'content' => [
                                'application/ld+json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/Credentials',
                                    ],
                                ],
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/Credentials',
                                    ],
                                ],
                            ],
                            "description" => "The new Credentials resource",
                        ],
                        // Swagger (OASv2)
//                        'parameters' => [
//                            [
//                                'name' => 'Credentials',
//                                'in' => "body",
//                                'description' => 'Create new JWT Token',
//                                'schema' => [
//                                    '$ref' => '#/components/schemas/Credentials',
//                                ],
//                            ],
//                        ],
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => 'Get JWT token',
                                'content' => [
                                    "application/ld+json" => [
                                        "schema" => [
                                            '$ref' =>  '#/components/schemas/Token',
                                        ],
                                    ],
                                    "application/json" => [
                                        "schema" => [
                                            '$ref' =>  '#/components/schemas/Token',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return array_merge_recursive($docs, $tokenDocumentation);
    }
}
