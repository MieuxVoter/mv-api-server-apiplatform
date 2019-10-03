<?php


namespace App\Security;


use App\Application;
use App\Entity\LimajuPoll;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


/**
 * This bouncer's job is to tell who can get in and who can't.
 *
 *
 * Class Bouncer
 * @package App\Security
 */
class PollBouncer extends Voter
{
    const CAN_DELETE = "can_delete";

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var Application
     */
    private $app;


    /**
     * Bouncer constructor.
     * @param Application $app
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        Application $app,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->app = $app;
    }

    public function canCurrentUserEditPoll(LimajuPoll $poll, string $password='') : bool
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ('' == $password) {
            if ( ! $this->authorizationChecker->isGranted('ROLE_USER')) {
                return false;
            } else {

            }
        } else {
            // TODO
        }

        return false;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if ( ! $subject instanceof LimajuPoll) {
            return false;
        }

        if ( ! in_array($attribute, array(self::CAN_DELETE))) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $roles = $token->getRoleNames();
        if (self::CAN_DELETE == $attribute) {
            if ($subject instanceof LimajuPoll) {
                if (in_array('ROLE_ADMIN', $roles)) {
                    return true;
                }

                if ($subject->getAuthor() === $this->app->getAuthenticatedUser()) {
                    return true;
                }
            }
        }

        return false;
    }
}