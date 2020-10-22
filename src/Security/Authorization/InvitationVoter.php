<?php


namespace App\Security\Authorization;


use App\Entity\Poll;
use App\Entity\User;
use App\Repository\PollInvitationRepository;
use App\Repository\PollRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


/**
 * Note: this is not a Voter in the political sense.
 * The word Voter comes from the Symfony ecosystem.
 *
 * Class InvitationVoter
 * @package App\Security\Authorization
 */
class InvitationVoter extends Voter
{
    const REQUEST_POLL_ID = "id";
    const CAN_CREATE_INVITATIONS = "can_create_invitations";

    /**
     * @var PollRepository
     */
    private $pollRepository;

    /**
     * @var PollInvitationRepository
     */
    private $invitationsRepository;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * Bouncer constructor.
     * @param PollRepository $pollRepository
     * @param PollInvitationRepository $invitationsRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param RequestStack $requestStack
     */
    public function __construct(
        PollRepository $pollRepository,
        PollInvitationRepository $invitationsRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        RequestStack $requestStack
    ) {
        $this->pollRepository = $pollRepository;
        $this->invitationsRepository = $invitationsRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->requestStack = $requestStack;
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
        // In the route that interests us,
        // $subject is a ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator
//        if ( ! ($subject instanceof InvitationCollection)) {
//            return false;
//        }

        // So we rely on a less generic attribute
        if ( ! in_array($attribute, array(self::CAN_CREATE_INVITATIONS))) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Poll $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if ( ! ($user instanceof User)) {
            return false;
        }

        switch ($attribute) {
            case self::CAN_CREATE_INVITATIONS:
                // Do we even *want* admins to be able to create invitations?
                // Seems like an anti-feature to me…  Disabled for now.
//                if (in_array('ROLE_ADMIN', $user->getRoles())) {
//                    return true;
//                }

                $request = $this->requestStack->getCurrentRequest();
                $pollId = $request->get(self::REQUEST_POLL_ID);

                if (null === $pollId) {
                    trigger_error(
                        "Using ".self::CAN_CREATE_INVITATIONS.
                        " on request without ".self::REQUEST_POLL_ID,
                        E_USER_WARNING
                    );
                    return false;
                }
                $poll = $this->pollRepository->findOneByUuid($pollId);

                if ($poll->getAuthor() === $user) {
                    return true;
                }

                break;
        }

        return false;
    }
}
