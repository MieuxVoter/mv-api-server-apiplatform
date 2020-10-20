<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */ // stuff will fail in here, it's the point.

use App\Application;
use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\User;
use App\Features\Actor;
use App\Features\Actors;
use App\Repository\PollProposalRepository;
use App\Repository\PollProposalBallotRepository;
use App\Repository\PollRepository;
use App\Repository\UserRepository;
use App\Tally\Bot\TallyBotInterface;
use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This context class contains the definitions of the steps used by the demo 
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * Properties of this class are NOT shared between feature contexts.
 * Except for kernel and context, who are injected dependencies.
 * We're using the injected context as shared context between different FeatureContext files.
 * 
 * @see http://behat.org/en/latest/quick_start.html
 */
class BaseFeatureContext extends WebTestCase implements Context
{

    use LanguageAwareFeatureTrait;


    /**
     * The version of the API to test against.
     * @var string
     */
    protected $version = "1";


    /**
     * A context bag for each actor, shared between all FeatureContexts.
     * Yes, sharing contexts is BAD. Please suggest another way forward :3
     * @var Actors
     */
    protected $actors;


    public function __construct(Actors $actors)
    {
        parent::__construct(); // data parameter?

        $this->actors = $actors;
    }


    /**
     * @return Actors
     */
    public function getActors(): Actors
    {
        return $this->actors;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ///
    /// SERVICES
    ///


    protected function getContainer(): ContainerInterface
    {
        if (null == static::$kernel) {
            throw new \Exception(__CLASS__."'s kernel is not instantiated yet.");
        }

        return static::$kernel->getContainer();
    }


    /**
     * Get service by id.
     *
     * @param string $id
     *
     * @return object
     */
    protected function get($id)
    {
        return $this->getContainer()->get($id);
    }


    /**
     * @return Application
     */
    protected function app()
    {
        return $this->get(Application::class);
    }


    /**
     * Get the entity manager.
     *
     * @return EntityManager
     */
    protected function getEntityManager() : EntityManager
    {
        return $this->get('doctrine')->getManager();
    }


    /**
     * Get the message bus
     *
     * @return MessageBusInterface
     */
    protected function getMessageBus() : MessageBusInterface
    {
        return $this->get('messenger.default_bus');
    }


//    /**
//     * Get the user manager from FOSUserBundle.
//     * Perhaps we'll move to msgphp_user at some point.
//     *
//     * @return UserManager
//     */
//    protected function getUserManager() : UserManager
//    {
//        return $this->get('fos_user.user_manager');
//    }


    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed The parameter value
     *
     * @throws Exception
     */
    protected function getParameter(string $name)
    {
        return $this->getContainer()->getParameter($name);
    }

    /**
     * @param $class
     * @return ObjectRepository|EntityRepository
     */
    protected function getRepository(string $class)
    {
        return $this->getEntityManager()->getRepository($class);
    }

    protected function getUserRepository() : UserRepository
    {
        return $this->get(UserRepository::class);
    }

    /**
     * @return PollRepository
     */
    protected function getPollRepository()
    {
        return $this->get(PollRepository::class);
    }

    /**
     * @return PollProposalRepository
     */
    protected function getLimajuPollProposalRepository()
    {
        return $this->get(PollProposalRepository::class);
    }

    /**
     * @return PollProposalBallotRepository
     */
    protected function getLimajuPollProposalVoteRepository()
    {
        return $this->get(PollProposalBallotRepository::class);
    }


    /**
     *
     *
     * @param string $tallyName
     * @return TallyBotInterface
     */
    protected function getTallyBot(string $tallyName) : TallyBotInterface
    {
        // TODO: I18N
        $tallyFileName = ucwords($tallyName);
        return $this->get("App\\Tally\\Bot\\${tallyFileName}TallyBot");
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// Reverse I18N
    ///

    public function unlocalizePollMention($localizedMention)
    {
        return $this->t("value.majority_judgment_poll.mention.$localizedMention");
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// Word to Number : a hurdle for I18N
    /// ----------------------------------
    ///
    /// We did not find a W2N library in PHP already supporting many languages.
    /// And daraeman/WordToNumber is the only W2N library we found in PHP.   Let's try it.
    /// This should move to its own Service, its own lib.
    /// - We need an international schelling point here for word <--> number conversion
    /// - Interface-Oriented Programming (+ Traits, why not, perhaps with Advisors)
    /// - Lots of reading to do ; at least PERL and papers
    /// - Interesting for a design jam, a wild one
    /// - Behat: looks like they saw the dragon and figured it was not worth the trouble
    /// - Should perhaps support the accessibility redundency like so : "Given three (3) things"

    /**
     * A tool to read numbers in their literal form.
     * Works in english and french.
     * Has known quirks, but should be okay for our uses.
     *
     *   number("forty thousand and three") // 40003
     *   number("mille sept cent quatre-vingt quinze") // 1795
     *   number("42") // 42
     *   number("invalid") // 0
     *
     * @param $stringNumber
     * @return int
     * @throws Exception
     */
    public function number($stringNumber)
    {
        // I18N hax
        if (preg_match("!^no(?:ne)?!ui", $stringNumber)) {
            $stringNumber = "zero";
        }
        if (preg_match("!^aucun⋅?e?!ui", $stringNumber)) {
            $stringNumber = "zero";
        }
        ///////////

        if (is_int($stringNumber) || is_float($stringNumber)) {
            return $stringNumber;
        }

        if ( ! is_string($stringNumber)) {
            var_dump($stringNumber);
            throw new Exception("Cannot parse number, it's not a string.  Dumped above.");
        }

        $intval = intval($stringNumber);
        if ($stringNumber === (string)$intval) {
            return $intval;
        }

        $map = [ // where's the map for this?
            'en'=>'english',
            'fr'=>'french',
        ];
        if ( ! isset($map[$this->language])) {
            throw new Exception(
                "You want a new language? That's great! \n" .
                "You need to implement a word-to-number conversion. \n".
                "We're using a fork of daraeman/WordToNumber for now. \n".
                "You're welcome to improve it or rewrite everything to suit your needs."
            );
        }
        $language = $map[$this->language];

        $w2n = new \daraeman\WordToNumber();
        $w2n->setLanguage($language);
        $n = $w2n->parse($stringNumber);

        if (false === $n) {
            throw new Exception("Cannot parse number '$stringNumber'.");
        }

        return intval($n);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// YAML
    /// ----
    ///
    /// Useful in the gherkin steps, as pystring, a multiline string delimited by `"""` (three double quotes).
    ///

    /**
     * Transform the YAML into its parsed form, ready to use as an object|array|whatever.
     *
     * @param $pystring
     * @return array
     */
    protected function yaml($pystring)
    {
        return Yaml::parse($pystring,
            Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE &
            Yaml::PARSE_OBJECT &
            Yaml::PARSE_OBJECT_FOR_MAP &
            Yaml::PARSE_DATETIME &
            Yaml::PARSE_CONSTANT
        );
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// DATABASE THINGIES

    protected function persist($entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    protected function flush()
    {
        $this->getEntityManager()->flush();
    }


    /**
     * How to move this into the main codebase and out of the test-suite ?
     * Also, this binds us to a relational architecture (by opposition to document)
     *
     * @param $what
     * @return mixed
     */
    protected function countEntities($what)
    {
        $count = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(e)')
            ->from(sprintf('%s', $what), 'e')
            ->getQuery()
            ->execute()[0][1];

        return $count;
    }


    public function thereShouldBeExactlyThatMuchEntitiesInTheDatabase($thatMuch, $what)
    {
        $thatMuch = $this->number($thatMuch);
        $count = $this->countEntities($what);

        if ($thatMuch != $count) {
            $this->fail("Found $count ${what}".((1 == $count)?'':'s')." instead of $thatMuch.");
        }
    }


    protected function findOnePollFromSubject($subject, $lenient = false) : ?Poll
    {
        $work = $this->getPollRepository()->findOneBySubject($subject);
        if (( ! $lenient) && (null == $work)) {
            $this->failTrans("no_majority_judgment_poll_found_for_title", ['title' => $subject]);
        }

        return $work;
    }


    protected function findOnePollProposalFromId($uuid, $lenient = false) : ?Proposal
    {
        $pollProposal = $this->getRepository(Proposal::class)->findOneByUuid($uuid);
        if (( ! $lenient) && (null == $pollProposal)) {
//            $this->fail("No Proposal with UUID `$uuid' could be found.");
            $this->failTrans("no_poll_proposal_found_for_uuid", ['uuid' => $uuid]);
        }

        return $pollProposal;
    }


    protected function findOnePollProposalFromTitleAndPoll($title, $poll, $lenient = false) : ?Proposal
    {
        /** @var Proposal $PollProposal
         */
        $PollProposal = $this->getRepository(Proposal::class)->findOneBy([
            'title' => $title,
            'poll' => $poll,
        ]);
        if (( ! $lenient) && (null == $PollProposal)) {
            $this->failTrans("no_majority_judgment_poll_proposal_found_for_title", [
                'title' => $title,
                'poll' => $poll,
            ]);
        }

        return $PollProposal;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//    protected function user($someone) : User
//    {
//        $citizen = $this->getUserRepository()->findByUsername($someone);
//
//        if (null == $citizen) {
//            try {
//                $citizen = $this->getUserRepository()->findUserBy(['id' => $someone]);
//            } catch (Doctrine\DBAL\Types\ConversionException $e) {} // UUID is strict
//        }
//
//        if (null == $citizen) {
//            $citizen = $this->getUserRepository()->doFindByFields(['pseudonym' => $someone]);
//        }
//
//        if (null == $citizen) {
//            $citizen = $this->getUserRepository()->findOneBy(['email' => $someone]);
//        }
//
//        if (null == $citizen) {
//            throw new \Exception("No citizen found for '${someone}'.");
//        }
//
//        return $citizen;
//    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    protected function createUser($name, $roles = []) : array
    {
        $identifier = uniqid("citizen-", true);
        $email = "${identifier}@users.mieuxvoter.fr";
        
        $password = md5(uniqid()); // security is irrelevant, since those are test users

        $user = new User();
        $user
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setUsername($name);

        if ( ! empty($roles)) {
            foreach ($roles as $role) {
                $user->addRole($role);
            }
        }

        // We use App\DataProvider\UserDataProvider::persist() to ensure password encryption
        $this->get("App\DataPersister\UserDataPersister")->persist($user);
//        $this->getEntityManager()->persist($user); // no, no

        return ['user' => $user, 'token' => $password];
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    ///
    /// ACTORS CONTEXTS
    /// Each Actor gets their own HTTP client and transaction log.
    /// Actors, as injected dependencies, are shared through FeatureContexts.
    /// (see behat.yaml to set up new services injection)
    ///


    /**
     * Each Actor gets their own HTTP client, their very own connection to the API.
     * To create an Actor in your scenario, use the step "Given a citizen named …".
     *
     * @param $actorName
     * @param bool $createIfNone
     * @return Actor
     */
    protected function actor($actorName, $createIfNone=false): Actor
    {
        return $this->getOrCreateActor($actorName, $createIfNone);
    }


    protected function getOrCreateActor($actorName, $createIfNone=true): Actor
    {
        $normalizedActorName = $this->normalizeActorName($actorName);

        if ( ! $this->getActors()->hasActor($normalizedActorName)) {
            if ($createIfNone) {
                $client = $this->createClient();
                $actor = new Actor();
                $actor->setClient($client);

                $this->getActors()->addActor($normalizedActorName, $actor);
            } else {
                throw new \Exception($this->t("testing.error.no_actor_found", ['actorName'=>$actorName]));
            }
        }

        return $this->getActors()->getActor($normalizedActorName);
    }


    protected function normalizeActorName(string $actorName) : string
    {
        $actorName = strtolower(trim($actorName));

        // fixme: we need something sane and scalable about this
        if ("j'" == $actorName) {
            $actorName = "je";
        }

        return $actorName;
    }


    public function iri($item)
    {
        return $this->app()->iri($item);
    }
}
