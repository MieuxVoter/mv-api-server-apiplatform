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

    public function __construct(NormalizerInterface $decorated, iterable $documenters)
    {
        $this->decorated = $decorated;
        $this->documenters = $documenters;
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

        return $docs;
    }
}
