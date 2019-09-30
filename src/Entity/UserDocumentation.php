<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;


// This class exists as a (hacky) way to provide documentation.


/**
 * You.
 *
 * @ApiResource(
 *     shortName="User",
 *     order=10,
 *     itemOperations={},
 *     collectionOperations={
 *         "post"={
 *             "controller"="App\Controller\RegistrationController",
 *         },
 *     },
 * )
 */
class UserDocumentation
{
    /**
     * @var string Primary e-mail address
     * @Groups({"user:read", "user:write"})
     */
    public $email;

    /**
     * @var string|null Plain password (Required in "write")
     * @Groups({"user:write"})
     */
    public $password;
}