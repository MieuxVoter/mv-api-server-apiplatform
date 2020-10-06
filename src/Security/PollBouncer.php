<?php


namespace App\Security;


use App\Application;
use App\Entity\Poll;
use App\Repository\PollProposalVoteRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


/**
 * This bouncer's job is to tell who can get in and who can't.
 *
 * Note: this is not a Voter in the political sense.
 * The word Voter comes from the symfony ecosystem, and means … well… bouncer, basically.
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
     * @var PollProposalVoteRepository
     */
    private $voteRepository;


    /**
     * Bouncer constructor.
     * @param Application $app
     * @param PollProposalVoteRepository $voteRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        Application $app,
        PollProposalVoteRepository $voteRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->app = $app;
        $this->voteRepository = $voteRepository;
        $this->authorizationChecker = $authorizationChecker;
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
        if ( ! $subject instanceof Poll) {
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
        switch ($attribute) {
            case self::CAN_DELETE:
                if ($subject instanceof Poll) {
                    if (in_array('ROLE_ADMIN', $roles)) {
                        return true;
                    }

                    if (0 < $this->voteRepository->countVotesOnPoll($subject)) {
                        return false;
                    }

                    if ($subject->getAuthor() === $this->app->getAuthenticatedUser()) {
                        return true;
                    }
                }

        }

        return false;
    }
}