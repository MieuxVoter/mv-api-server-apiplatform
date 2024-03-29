<?php

/**
 * Annotations for linters, code inspectors, etc.
 * @noinspection PhpDocSignatureInspection
 */

namespace Features;

use App\Entity\Poll;
use App\Entity\Poll\Grade;
use App\Entity\Poll\Proposal;
use App\Entity\User;
use App\Ranking\Settings\MajorityJudgmentSettings;


/**
 * This context class contains definitions of some of the more general-purpose steps.
 *
 * This is also a blackboard for new step defs, until we figure out where to store them.
 */
class MainFeatureContext extends BaseFeatureContext
{
    //   _____ _                    _______       _                        ___
    //  / ____(_)                  / /_   _|     | |                      | \ \
    // | |  __ ___   _____ _ __   | |  | |  _ __ | |_ ___ _ __ _ __   __ _| || |
    // | | |_ | \ \ / / _ \ '_ \  | |  | | | '_ \| __/ _ \ '__| '_ \ / _` | || |
    // | |__| | |\ V /  __/ | | | | | _| |_| | | | ||  __/ |  | | | | (_| | || |
    //  \_____|_| \_/ \___|_| |_| | ||_____|_| |_|\__\___|_|  |_| |_|\__,_|_|| |
    //                             \_\                                      /_/


    /**
     * @Given /^un(?:⋅?e)? (?:visit(?:eu[rs]⋅?e?|rice))(?: .*)? (?:sur)?nommé(?:⋅?e)? (?P<name>.+)$/ui
     * @Given /^a visitor named (?P<name>.+)$/ui
     * @throws \Exception
     */
    public function givenVisitorNamed($name)
    {
        $this->actor($name, true);
    }


    /**
     * @Given /^un(?:⋅?e)? (?:juge|utilisat(?:eure?|rice)|élect(?:eure?|rice)|citoyen(?:⋅?ne)?)(?: .*)? (?:sur)?nommé(?:⋅?e)? (?P<name>.+)$/ui
     * @Given /^a citizen named (?P<name>.+)$/ui
     * @throws \Exception
     */
    public function givenCitizenNamed($name)
    {
        $userAndToken = $this->createUser($name);

        $actor = $this->actor($name, true);
        $actor->setUser($userAndToken['user']);
        $actor->setPassword($userAndToken['token']);
    }


    /**
     * @Given /^(?P<actor>.+?) (?:suis|est?) un(?:⋅?e)? citoyen(?:⋅?ne)? nommé(?:⋅?e)? (?P<name>.+)$/u
     * @Given /^(?P<actor>.+?) (?:am|is|are) (?:a|the) citizen named (?P<name>.+)$/u
     * @throws \Exception
     */
    public function givenActorIsCitizenNamed($actor, $name)
    {
        $userAndToken = $this->createUser($name);

        $actor = $this->actor($actor, true);
        $actor->setUser($userAndToken['user']);
        $actor->setPassword($userAndToken['token']);
    }


    /**
     * @Given /^un(?:⋅?e)? modérat(?:eur[⋅.]?e?|rice)(?: .*?)? (?:sur)?nommé(?:⋅?e)? (?P<name>.+)$/ui
     * @Given /^a moderator named (?P<name>.+)$/ui
     * @throws \Exception
     */
    public function givenModeratorNamed($name)
    {
        $userAndToken = $this->createUser($name, ['ROLE_ADMIN']);

        $actor = $this->actor($name, true);
        $actor->setUser($userAndToken['user']);
        $actor->setPassword($userAndToken['token']);
    }


    /**
     * @Given /^un scrutin(?: au jugement majoritaire)? comme suit:?$/ui
     * @Given /^a(?: majority judgment)? poll like so:?$/ui
     */
    public function givenPollLikeSo($pystring)
    {
        $authorKey = $this->t('keys.poll.author');
        $scopeKey = $this->t('keys.poll.scope');
        $subjectKey = $this->t('keys.poll.subject');
        $proposalsKey = $this->t('keys.poll.proposals');
        $gradesKey = $this->t('keys.poll.grades');
        $data = $this->yaml($pystring);

        $uuid = null;
        if ( isset($data['uuid'])) {
            $uuid = \Ramsey\Uuid\Uuid::fromString($data['uuid']);
        }

        $poll = new Poll($uuid);

        if ( isset($data['slug'])) {
            $poll->setSlug($data['slug']);
        }

        if ( ! isset($data[$subjectKey])) {
            $this->failTrans("poll_has_no_subject");
        }
        $poll->setSubject($data[$subjectKey]);

        if (isset($data[$scopeKey])) {
            $poll->setScope($this->t('values.poll.scope.'.$data[$scopeKey]));
        }

        if (isset($data[$authorKey])) {
            $author = $this->actor($data[$authorKey], false);
            $user = $this->getRepository(User::class)->find($author->getUser()->getId());
            $poll->setAuthor($user);
        }

        $this->persist($poll);

        if ( ! isset($data[$proposalsKey])) {
            $this->failTrans("poll_has_no_proposal", ['proposalsKey' => $proposalsKey]);
        }

        foreach ($data[$proposalsKey] as $candidateTitle) {
            $proposal = new Proposal();
            $proposal->setTitle($candidateTitle);
            $poll->addProposal($proposal);
            $this->persist($proposal);
        }

        if ( ! isset($data[$gradesKey])) {
            $this->failTrans("poll_has_no_grades", ['gradesKey' => $gradesKey]);
        }

        foreach ($data[$gradesKey] as $k => $gradeName) {
            $grade = new Grade();
            $grade->setName($gradeName);
            $grade->setLevel($k);
            $poll->addGrade($grade);
            $this->persist($grade);
        }

        $this->flush();
    }


    //                             _      _______       _                        ___
    //     /\                     | |    / /_   _|     | |                      | \ \
    //    /  \   ___ ___  ___ _ __| |_  | |  | |  _ __ | |_ ___ _ __ _ __   __ _| || |
    //   / /\ \ / __/ __|/ _ \ '__| __| | |  | | | '_ \| __/ _ \ '__| '_ \ / _` | || |
    //  / ____ \\__ \__ \  __/ |  | |_  | | _| |_| | | | ||  __/ |  | | | | (_| | || |
    // /_/    \_\___/___/\___|_|   \__| | ||_____|_| |_|\__\___|_|  |_| |_|\__,_|_|| |
    //                                   \_\                                      /_/
    //


    /**
     * @Then /^(?:qu')?il(?: ne)? d(?:oi|evrai)t(?: maintenant)? y avoir (?P<thatMuch>.+) utilisat(?:rice|eur(?:⋅?e)?)s? dans la base de données$/ui
     * @Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) users? in the database$/ui
     */
    public function thereShouldBeSomeUsersInTheDatabase($thatMuch)
    {
        $this->thereShouldBeExactlyThatMuchEntitiesInTheDatabase($thatMuch, User::class);
    }


    /**
     * @Then /^(?:qu')?il(?: ne)? d(?:oi|evrai)t(?: maintenant)?(?: encore)?(?: toujours)? (?:n')?y avoir (?P<thatMuch>.+) scrutins?(?: au jugement majoritaire)? dans la base de données$/ui
     * @Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) majority judgment polls? in the database$/ui
     */
    public function thereShouldBeSomePollsInTheDatabase($thatMuch)
    {
        $this->thereShouldBeExactlyThatMuchEntitiesInTheDatabase($thatMuch, Poll::class);
    }


    /**
     * fixme: en step
     * @Then /^le scrutin(?: au jugement majoritaire)? intitulé "(?P<pollSubject>.+?)" d(?:oi|evrai)t(?: maintenant)?(?: encore)? avoir (?P<thatMuch>.+) propositions?$/ui
     * @throws \Exception
     */
    public function thereShouldBeSomeProposalsInThePoll($thatMuch, $pollSubject)
    {
        $thatMuch = $this->number($thatMuch);
        $poll = $this->findOnePollFromSubject($pollSubject);
        $actual = count($poll->getProposals());

        $this->assertEquals($thatMuch, $actual);
    }


    /**
     * fixme: en step
     * @Then /^le scrutin(?: au jugement majoritaire)? (?:intitulé|assujettissant) "(?P<pollSubject>.+?)" d(?:oi|evrai)t(?: maintenant)?(?: encore)? avoir (?P<thatMuch>.+) mentions?$/ui
     * @throws \Exception
     */
    public function thereShouldBeSomeGradesInThePoll($thatMuch, $pollSubject)
    {
        $thatMuch = $this->number($thatMuch);
        $poll = $this->findOnePollFromSubject($pollSubject);
        $actual = count($poll->getGrades());

        $this->assertEquals($thatMuch, $actual);
    }


    /**
     * fixme: en step
     * Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) majority judgment polls? in the database$/ui
     * @Then /^(?:que?' ?)?(?P<actor>.+?)(?: ne)? d(?:oi|evrai)t(?: maintenant)?(?: encore)? avoir (?P<thatMuch>.+) votes? sur le scrutin(?: au jugement majoritaire)? (?:titré|intitulé|assujetti(?:ssant)?) "(?P<title>.+?)"$/ui
     * @throws \Exception
     */
    public function actorShouldHaveSomePollProposalVotesForPoll($actor, $thatMuch, $title)
    {
        $actor = $this->actor($actor);
        $thatMuch = $this->number($thatMuch);
        $poll = $this->findOnePollFromSubject($title);

        $proposals = $poll->getProposals();
        $ballots = $this->getBallotRepository()->findBy([
            'participant' => $actor->getUser()->getId(),
            'proposal' => array_map(function(Proposal $item){
                return $item->getId();
            }, $proposals->toArray()),
        ]);
        $actual = count($ballots);

        // Does the job, but no I18N support.
        //$this->assertEquals($thatMuch, $actual);

        if ($thatMuch !== $actual) {  // are we seriously going to rewrite PHPUnit with I18N? No, I don't think so.
            $this->failTrans('not_equal', ['expected' => $thatMuch, 'actual' => $actual]);
        }
    }


    /**
     * This step is too long and needs refactoring and simplification.
     *
     * fixme: en step
     * @Then /^le dépouillement(?: de)? (?P<tally>standard|) ?du scrutin(?: au jugement majoritaire)? (?:de|titré|intitulé|assujetti(?:ssant)?) "(?P<pollSubject>.*)" devrait être *:?$/u
     */
    public function theTallyOfThePollTitledShouldBeLikeYaml($tally, $pollSubject, $pystring)
    {
        $expectedRaw = $this->yaml($pystring);
        $poll = $this->findOnePollFromSubject($pollSubject);

        $gradeAtom = $this->t('keys.proposal.grade');
        $rankAtom = $this->t('keys.proposal.rank');

        $expected = [];
        $pollProposals = [];
        foreach ($expectedRaw as $proposalTitle => $gradeOrData) {
            $pollProposal = $this->findOneProposalFromTitleAndPoll($proposalTitle, $poll);
            $pollProposalId = $pollProposal->getUuid()->toString();
            $pollProposals[$pollProposalId] = $pollProposal;

            if ( ! is_array($gradeOrData)) {
                $gradeOrData = [
                    $gradeAtom => $gradeOrData,
                ];
            }

            $grade = $this->getGradeRepository()->findOneByPollAndName($poll, $gradeOrData[$gradeAtom]);
            $gradeOrData[$gradeAtom] = $grade->getUuid()->toString();
//            $gradeOrData[$gradeAtom] = $this->unlocalizePollMention($gradeOrData[$gradeAtom]);
            $expected[$pollProposalId] = $gradeOrData;
        }

        $deliberator = $this->getRankingService('Majority Judgment');
//        $tallier = $this->getTallyBot($tally);
//        $actual = $tallier->tally($poll);
        /** @var Poll\Result $actual */
        $actual = $deliberator->resolve($poll, new MajorityJudgmentSettings());

        $assertedSomething = false;
        $expectationsLeftToProcess = array_keys($expected);

        foreach ($actual->getLeaderboard() as $proposalTally) {
            /** @var Proposal\Result $proposalTally */
            $proposalUuid = $proposalTally->getProposal()->getUuid()->toString();

            if (isset($expected[$proposalUuid])) {
                $assertedSomething = true;
                $expectationsLeftToProcess = array_diff($expectationsLeftToProcess, [$proposalUuid]);

                $pollProposal = $this->findOnePollProposalFromId($proposalUuid);

                if ($expected[$proposalUuid][$gradeAtom] !== $proposalTally->getMedianGrade()->getUuid()->toString()) {
                    //dump("Actual proposal tally", $proposalTally);
                    $this->failTrans("proposal_median_grade_mismatch", [
                        'expected_grade' => $expected[$proposalUuid][$gradeAtom],
                        'actual_grade' => $proposalTally->getMedianGrade()->getUuid()->toString(),
                        'proposal' => $pollProposal,
                    ]);
                }

                if (isset($expected[$proposalUuid][$rankAtom])) {
                    if ($expected[$proposalUuid][$rankAtom] !== $proposalTally->getRank()) {
                        dump("Actual poll tally", $actual);
                        $this->failTrans('proposal_rank_mismatch', [
                            'expected_rank' => $expected[$proposalUuid][$rankAtom],
                            'actual_rank' => $proposalTally->getRank(),
                            'proposal' => $pollProposal,
                        ]);
                    }
                }

            }
        }

        if (0 < count($expectationsLeftToProcess)) {
            $candidatesLeft = array_map(function($e) use ($pollProposals) {
                return $pollProposals[$e];
            }, $expectationsLeftToProcess);
            $this->failTrans("candidates_left_unprocessed", [
                'expected' => $expected,
                'candidates' => $candidatesLeft,
            ]);
        }

        if ( ! $assertedSomething) {
            $this->fail("You did not assert anything in this step?");
        }
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //                             _      ________      _                        ___
    //     /\                     | |    / /  ____|    | |                      | \ \
    //    /  \   ___ ___  ___ _ __| |_  | || |__  __  _| |_ ___ _ __ _ __   __ _| || |
    //   / /\ \ / __/ __|/ _ \ '__| __| | ||  __| \ \/ / __/ _ \ '__| '_ \ / _` | || |
    //  / ____ \\__ \__ \  __/ |  | |_  | || |____ >  <| ||  __/ |  | | | | (_| | || |
    // /_/    \_\___/___/\___|_|   \__| | ||______/_/\_\\__\___|_|  |_| |_|\__,_|_|| |
    //                                   \_\                                      /_/
    //


    /**
     * @Then /^(?P<actor>.+?)(?: ne)? devr(?:ai[st]|aient|ions)(?: encore| aussi)? avoir(?: qu[e'])? ?(?P<amount>.+?) invitations?$/iu
     * @Then /^(?P<actor>.+?) should(?: now)? have (?P<amount>.+?) invitations?$/iu
     * @throws \Exception
     */
    public function actorShouldHaveInvitations($actor, $amount)
    {
        $actor = $this->actor($actor);
        $amount = $this->number($amount);

        $this->assertEquals($amount, $actor->countInvitations());
    }


    /**
     * @Then /^(?P<actor>.+?)(?: ne)? devr(?:ai[st]|aient|ions)(?: encore| aussi)? voir(?: seulement| qu[e'])? ?(?P<amount>.+?) scrutins?$/iu
     * @Then /^(?P<actor>.+?) should(?: now)? see (?P<amount>.+?) polls?$/iu
     * @throws \Exception
     */
    public function actorShouldSeeThatManyPolls($actor, $amount)
    {
        $this->actorShouldSeeThatManyEntities($actor, $amount, 'Poll');
    }


    /**
     * @param $actor string
     * @param $amount string
     * @param $entityClass string The short name of the class as used by ApiPlatform
     * @throws \Exception
     */
    public function actorShouldSeeThatManyEntities($actor, $amount, $entityClass)
    {
        $actor = $this->actor($actor);
        $amount = $this->number($amount);
        $actual = $actor->getLastTransaction()->getResponseJson();

        $this->assertEquals("/contexts/".$entityClass, $actual['@context']);
        $this->assertEquals($amount, $actual['hydra:totalItems'], "The amount of seen entities is incorrect");
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @Then /^(?P<actor>.+?) devr(?:ai[st]|aient|ions)(?: encore| aussi)? (?:re[cç]e)?voir *:?$/ui
     */
    public function actorShouldReceive($actor, $pystring)
    {
        $actor = $this->actor($actor);
        $expected = (string) $pystring;
        $actual = $actor->getLastTransaction()->getResponse()->getContent();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @Then /^(?P<actor>.+?) g[ée]n[èe]r(?:e[s]?|ent|ons) (?:un|le) SVG d[ue] profil de mérite d'un scrutin comme suit *:?$/ui
     */
    public function actorGeneratesMeritProfileSvg($actor, $pystring)
    {
        $actor = $this->actor($actor);
        $parameters = $this->yaml($pystring) ?? [];

        $actor->api("GET", "/render/merit-profile.svg", [], $parameters);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    // …

}
