<?php

declare(strict_types=1);

namespace App\Swagger;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


final class JwtDecorator implements NormalizerInterface
{
    /** @var NormalizerInterface $decorated */
    private $decorated;

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

//        dump($format);
//        "json"
//        dump($context);
//        array:2 [
//          "spec_version" => 2
//          "api_gateway" => false
//        ]

        $docs['definitions']['Token'] =  # OASv2
        $docs['components']['schemas']['Token'] = [  # OASv3
            'type' => 'object',
            'description' => 'An authentication token (JWT) for the `Authorization: Bearer` header.',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ];

        $docs['definitions']['Credentials'] =  # OASv2
        $docs['components']['schemas']['Credentials'] = [  # OASv3
            'type' => 'object',
            'description' => "User credentials to submit in order to get a perishable authentication token (JWT).",
            'properties' => [
                'usernameOrEmail' => [
                    'type' => 'string',
                    'example' => 'michel',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => '~5Up3®$3cR3741337',
                ],
            ],
        ];

        $tokenDocumentation = [
            'paths' => [
                '/_jwt' => [
                    'post' => [
                        'tags' => ['Login', 'User'],
                        'operationId' => 'postCredentialsItem',
                        'summary' => 'Login using user credentials in order to get a JWT.',
                        // OASv3
                        'requestBody' => [
                            "description" => "User Credentials",
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
                        ],
                        // OASv2
                        'parameters' => [
                            [
                                'name' => 'Credentials',
                                'in' => "body",
                                'description' => 'User Credentials',
                                'schema' => [
                                    '$ref' => '#/components/schemas/Credentials',
                                ],
                            ],
                        ],
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => 'A JSON Web Token (JWT)',
                                // OASv3
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
                                // OASv2
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ],
                            ],
//                            Response::HTTP_UNAUTHORIZED => [
//                                'description' => 'Unauthorized credentials.',
//                            ],
                            Response::HTTP_BAD_REQUEST => [
                                'description' => 'Bad credentials.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return array_merge_recursive($docs, $tokenDocumentation);
    }
}
