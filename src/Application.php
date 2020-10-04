<?php


namespace App;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;


/**
 * The purpose of the methods in here is to be shared between :
 * - REST API Controllers
 * - GraphQL API Resolvers
 * - Feature Contexts
 *
 * Trying this coupling design pattern.  May come to regret it.
 * We should perhaps put as few methods as we can in this class.
 * We should move as many methods as we can out of this class.
 * We'll see where this goes…
 *
 * Class Application
 * @package App
 */
class Application
{
    /**
     * @var Security
     */
    protected $security;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserRepository
     */
    // protected $userRepository;

    /**
     * @var IriConverterInterface
     */
    protected $iriConverter;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * Application constructor.
     * This is going to get almost ALL services. Smelly smelly!
     *
     * @param Security $security
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param IriConverterInterface $iriConverter
     */
    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        // UserRepository $userRepository,
        IriConverterInterface $iriConverter,
        MessageBusInterface $messageBus
    )
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->iriConverter = $iriConverter;
        // $this->userRepository = $userRepository;
        $this->messageBus = $messageBus;
    }


    /**
     * Return the authenticated User, if any.
     *
     * @return User|null
     */
    public function getAuthenticatedUser(): ?User
    {
        /** @var UserIdentity $userIdentity */
        $userIdentity = $this->security->getUser();

        /** @var User|null $user */
        $user = $this->userRepository->findByUsername($userIdentity->getOriginUsername());

        return $user;
    }


    /**
     * @return IriConverterInterface
     */
    public function getIriConverter(): IriConverterInterface
    {
        return $this->iriConverter;
    }


    /**
     * Get the IRI of the provided $item, usually an Entity instance.
     *
     * @param $item
     * @return string
     */
    public function iri($item)
    {
        return $this->getIriConverter()->getIriFromItem($item);
    }


    /**
     * @return MessageBusInterface
     */
    public function getMessageBus(): MessageBusInterface
    {
        return $this->messageBus;
    }

}