<?php
/** @noinspection PhpUnusedAliasInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace App\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Swagger\DocumenterInterface;


/**
 * Extends the documentation with any Services tagged as `oas_documenter`.
 * Which, in our current configuration, means implementing `DocumenterInterface`.
 *
 * Documenter ~= Doxxer?
 *
 * Class SwaggerDecorator
 * @package App\Swagger
 */
final class SwaggerDecorator implements NormalizerInterface
{
    /** @var NormalizerInterface $decorated */
    private $decorated;

    /**
     * A collection of DocumenterInterface
     * @var DocumenterInterface[]
     */
    private $documenters;

    /**
     * @var array
     */
    private $extra_v2;

    /**
     * @var array
     */
    private $extra_v3;

    public function __construct(NormalizerInterface $decorated, iterable $documenters, array $extra_v2, array $extra_v3)
    {
        $this->decorated = $decorated;
        $this->documenters = $documenters;
        $this->extra_v2 = $extra_v2;
        $this->extra_v3 = $extra_v3;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
//        dump($format);
//        "json"

//        dump($context);
//        array:2 [
//          "spec_version" => 2
//          "api_gateway" => false
//        ]

        $docs = $this->decorated->normalize($object, $format, $context);

        foreach ($this->documenters as $documenter) {
            /** @var DocumenterInterface $documenter */
            $docs = $documenter->document($docs, $object, $format, $context);
        }

        if (2 == $context['spec_version']) {
            $docs = array_merge_recursive($docs, $this->extra_v2);
        }
        if (3 == $context['spec_version']) {
            $docs = array_merge_recursive($docs, $this->extra_v3);
        }

        return $docs;
    }
}
