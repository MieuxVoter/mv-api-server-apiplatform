<?php


namespace App\Controller;


use App\Entity\Poll\Invitation;
use App\Entity\User;
use App\Service\UsernameGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * See \App\Entity\User where this controller is declared and configured.
 *
 * We hook the natural resource POST to add our logic.
 *
 * Class RegisterUserController
 * @package App\Controller
 */
class RegisterUserController
{
//    use Is\EntityAware;
//    use Is\UserAware;

    /**
     * @var UsernameGenerator
     */
    protected $usernameGenerator;

    public function __construct(UsernameGenerator $usernameGenerator)
    {
        $this->usernameGenerator = $usernameGenerator;
    }

    /**
     * The `User` parameter MUST be named $data.  See https://api-platform.com/docs/core/controllers/
     *
     * @param User $data
     * @return User|null
     * @throws \Exception
     */
    public function __invoke(User $data)
    {
        if (empty($data->getUsername())) {
            $data->setUsername($this->usernameGenerator->generateUsername(true));
        }

        return $data;
    }
}
