<?php
namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * 
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->em = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * 
     *
     * @param $data
     * @param array $context
     * @return boolean
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     *
     * @param User $data
     * @param array $context
     * @return User
     */
    public function persist($data, array $context = [])
    {
        if ($data->getPlainPassword()) {
            
            $data->setPassword(
                $this->userPasswordEncoder->encodePassword($data, $data->getPlainPassword())
            );
            $data->eraseCredentials();
        }
        $this->em->persist($data);
        $this->em->flush();
    }

    /**
     * @param User $data
     * @param array $context
     * @return void
     */
    public function remove($data, array $context = [])
    {
        $this->em->remove($data);
        $this->em->flush();
    }
}