<?php


namespace App\Swagger\Documenter;


use App\Swagger\DocumenterInterface;
use Symfony\Component\HttpFoundation\Response;


/** @noinspection PhpUnused */


class JwtDocumenter implements DocumenterInterface
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
        $version = $context['spec_version'];

        $tokenSchema = [
            'type' => 'object',
            'description' => 'An authentication token ([JWT](https://jwt.io/)) for the `Authorization: Bearer` header.',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ];

        $credentialsSchema = [
            'type' => 'object',
            'description' => "User credentials to submit to the login endpoint in order to get a perishable authentication token (Json Web Token).",
            'properties' => [
                'usernameOrEmail' => [
                    'type' => 'string',
                    'example' => 'michel',
                    'description' => 'The email or username you chose during registration.',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => '~5Up3®$3cR3741337',
                    'description' => 'The password or passphrase you chose during registration.',
                ],
            ],
        ];

        switch ($version) {
            case 2:
                $docs['definitions']['Token'] = $tokenSchema;
                $docs['definitions']['Credentials'] = $credentialsSchema;
                break;
            case 3:
            default:
                $docs['components']['schemas']['Token'] = $tokenSchema;
                $docs['components']['schemas']['Credentials'] = $credentialsSchema;
        }


        $tokenDocumentation = [
            'paths' => [
                '/_jwt' => [
                    'post' => [
                        'tags' => ['Login', 'User'],
                        'operationId' => 'postCredentialsItem',
                        'summary' => "Returns an authentication Token from login Credentials.",
                        'description' => "Creating and participating to private polls require authentication.  The Token returned is a [JWT](https://jwt.io/) valid for one hour.",
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => 'A JSON Web Token (JWT)',
                            ],
                            Response::HTTP_BAD_REQUEST => [
                                'description' => 'Bad credentials.',
                            ],
                            Response::HTTP_UNAUTHORIZED => [
                                'description' => 'Unauthorized credentials.',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        switch ($version) {
            case 2:
                $tokenDocumentation = array_merge_recursive($tokenDocumentation, [
                    'paths' => [
                        '/_jwt' => [
                            'post' => [
                                'consumes' => [
                                    'application/ld+json',
                                    'application/json',
                                ],
                                'produces' => [
                                    'application/ld+json',
                                    'application/json',
//                                    'text/html',
                                ],
                                'parameters' => [
                                    [
                                        'name' => 'Credentials',
                                        'in' => "body",
                                        'description' => 'User Credentials',
                                        'schema' => [
                                            '$ref' => '#/definitions/Credentials',
                                        ],
                                    ],
                                ],
                                'responses' => [
                                    Response::HTTP_OK => [
                                        'content' => [
                                            "application/ld+json" => [
                                                "schema" => [
                                                    '$ref' =>  '#/definitions/Token',
                                                ],
                                            ],
                                            "application/json" => [
                                                "schema" => [
                                                    '$ref' =>  '#/definitions/Token',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
                break;
            case 3:
            default:
                $tokenDocumentation = array_merge_recursive($tokenDocumentation, [
                    'paths' => [
                        '/_jwt' => [
                            'post' => [
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
                                'responses' => [
                                    Response::HTTP_OK => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/Token',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        }

        return array_merge_recursive($docs, $tokenDocumentation);
    }
}