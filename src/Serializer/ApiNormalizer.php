<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Poll\Invitation;
use App\Security\PermissionsReferee;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;


final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
    }

    /** @var PermissionsReferee $referee */
    protected $referee;

    /**
     * @required
     * @param PermissionsReferee $referee
     */
    public function setReferee(PermissionsReferee $referee): void
    {
        $this->referee = $referee;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ( ! is_array($data)) {
            return $data;
//            $data['date'] = date(\DateTime::RFC3339);
        }

        // This code has got to be moved to its own file.
        // Now we need tagged services (but can/should we change what's injected in here?)
        // Kind of like we did with the Documenter, but that was our own service,
        // this ApiNormalizer is replacing a service already: api_platform.jsonld.normalizer.item
        if ($object instanceof Invitation) {
//            $acceptedByYou = false;
            $acceptedByYou = $this->referee->isInvitationAcceptedByYou($object);
            $data['acceptedByYou'] = $acceptedByYou;
        }
        ////////////////////////////////////////////////////////////////////////

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }

}