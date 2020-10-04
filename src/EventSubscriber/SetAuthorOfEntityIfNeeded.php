<?php


namespace App\EventSubscriber;


use ApiPlatform\Core\EventListener\EventPriorities;
use App\Application;
use App\Entity\Poll;
use App\Entity\PollProposalVote;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;


/**
 * This is how we hook in API Platform to set the author, during creation, of
 * - Poll
 * - PollProposalVote
 *
 * This only works for REST
 * https://github.com/api-platform/api-platform/issues/734
 *
 * Documentation: https://api-platform.com/docs/core/events/#custom-event-listeners
 */
final class SetAuthorOfEntityIfNeeded implements EventSubscriberInterface
{
    /**
     * @var Application
     */
    private $application;

    /**
     * SetAuthorOfEntityIfNeeded constructor.
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setAuthorIfNeeded', EventPriorities::PRE_WRITE],
//            KernelEvents::REQUEST => ['setAuthorIfNeeded2', EventPriorities::POST_DESERIALIZE],
        ];
    }

    public function setAuthorIfNeeded(ViewEvent $event)
    {
        $entity = $event->getControllerResult();
//        $method = $event->getRequest()->getMethod();


        if ($entity instanceof Poll) {
            $entity->setAuthor($this->application->getAuthenticatedUser());
        }

        if ($entity instanceof PollProposalVote) {
            $entity->setElector($this->application->getAuthenticatedUser());
        }
    }
}