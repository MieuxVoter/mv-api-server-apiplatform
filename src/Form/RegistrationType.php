<?php

declare(strict_types=1);

namespace App\Form;

use MsgPhp\User\Infrastructure\Form\Type\HashedPasswordType;
use MsgPhp\User\Infrastructure\Validator\UniqueUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * IMPORTANT NOTE
 * HashedPasswordType expects password to ba an associative array like so: ['plain'=>'$3çREt!'}
 *
 * Class RegistrationType
 * @package App\Form
 */
final class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
                new UniqueUsername(),
            ],
        ]);
        $builder->add('password', HashedPasswordType::class, [
            'password_options' => [
                'constraints' => [
                    new NotBlank(),
                    new Length(['max'=>1024]),
                ],
            ],
        ]);
    }
}
