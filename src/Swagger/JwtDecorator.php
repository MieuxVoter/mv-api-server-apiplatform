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
                'username' => [
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
                '/api/authentication_token' => [
                    'post' => [
                        'tags' => ['Token'],
                        'operationId' => 'postCredentialsItem',
                        'summary' => 'Get JWT token to login.',
                        'parameters' => [
                            [
                                'name' => 'Credentials',
                                'in' => 'body',
                                'description' => 'Create new JWT Token',
                                'schema' => [
                                    '$ref' => '#/components/schemas/Credentials',
                                ],
                            ],
                        ],
                        'responses' => [
                            Response::HTTP_OK => [
                                'description' => 'Get JWT token',
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        dump(array_merge_recursive($docs, $tokenDocumentation));
        return array_merge_recursive($docs, $tokenDocumentation);
    }
}