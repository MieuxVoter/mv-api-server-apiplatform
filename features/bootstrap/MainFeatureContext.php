<?php

/**
 * Annotations for linters, code inspectors, etc.
 * @noinspection PhpDocSignatureInspection
 */


use App\Entity\Grade;
use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\User;


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
     * @Given /^un(?:⋅?e)? (?:utilisat(?:eure?|rice)|élect(?:eure?|rice)|citoyen(?:⋅?ne)?)(?: .*)? (?:sur)?nommé(?:⋅?e)? (?P<name>.+)$/ui
     * @Given /^a citizen named (?P<name>.+)$/ui
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
     * @Given /^a majority judgment poll like so:?$/ui
     */
    public function givenPollLikeSo($pystring)
    {
        $subjectKey = $this->t('keys.poll.subject');
        $proposalsKey = $this->t('keys.poll.proposals');
        $gradesKey = $this->t('keys.poll.grades');
        $data = $this->yaml($pystring);

        $poll = new Poll();

        if ( ! isset($data[$subjectKey])) {
            $this->failTrans("poll_has_no_subject");
        }

        $poll->setSubject($data[$subjectKey]);
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
     * @Then /^(?:qu')?il(?: ne)? d(?:oi|evrai)t(?: maintenant)? y avoir (?P<thatMuch>.+) utilisateur(?:⋅?e)?s? dans la base de données$/ui
     * @Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) users? in the database$/u
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
     */
    public function actorShouldHaveSomePollProposalVotesForPoll($actor, $thatMuch, $title)
    {
        $actor = $this->actor($actor);
        $thatMuch = $this->number($thatMuch);
        $poll = $this->findOnePollFromSubject($title);
        // fixme: for poll

        $votes = $this->getLimajuPollProposalVoteRepository()->findBy([
            'elector' => $actor->getUser()->getId(),
//            'poll' => $poll,
        ]);
        $actual = count($votes);

        // Does the job, but no I18N support.
        //$this->assertEquals($thatMuch, $actual);

        if ($thatMuch !== $actual) {  // are we seriously going to rewrite PHPUnit with I18N? No, I don't think so.
            $this->failTrans('not_equal', ['expected' => $thatMuch, 'actual' => $actual]);
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * This step is too long and needs refactoring and simplification.
     *
     * fixme: en step
     * @Then /^le dépouillement(?: de)? (?P<tally>standard) du scrutin(?: au jugement majoritaire)? (?:titré|intitulé|assujetti(?:ssant)?) "(?P<pollSubject>.*)" devrait être *:?$/u
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
            $pollProposal = $this->findOnePollProposalFromTitleAndPoll($proposalTitle, $poll);
            $pollProposalId = $pollProposal->getUuid()->toString();
            $pollProposals[$pollProposalId] = $pollProposal;

            if ( ! is_array($gradeOrData)) {
                $gradeOrData = [
                    $gradeAtom => $gradeOrData,
                ];
            }

//            $gradeOrData[$gradeAtom] = $this->unlocalizePollMention($gradeOrData[$gradeAtom]);
            $expected[$pollProposalId] = $gradeOrData;
        }

        $tallyBot = $this->getTallyBot($tally);
        $actual = $tallyBot->tallyVotesOnPoll($poll);

        $assertedSomething = false;
        $expectationsLeftToProcess = array_keys($expected);

        foreach ($actual->proposals as $proposalTally) {

            $proposalUuid = $proposalTally->poll_proposal_id->toString();

            if (isset($expected[$proposalUuid])) {
                $assertedSomething = true;
                $expectationsLeftToProcess = array_diff($expectationsLeftToProcess, [$proposalUuid]);

                $pollProposal = $this->findOnePollProposalFromId($proposalUuid);

                if ($expected[$proposalUuid][$gradeAtom] !== $proposalTally->median_grade) {
                    //dump("Actual proposal tally", $proposalTally);
                    $this->failTrans("proposal_tallies_dont_match", [
                        'expected_grade' => $expected[$proposalUuid][$gradeAtom],
                        'actual_grade' => $proposalTally->median_grade,
                        'proposal' => $pollProposal,
                    ]);
                }

                if (isset($expected[$proposalUuid][$rankAtom])) {
                    if ($expected[$proposalUuid][$rankAtom] !== $proposalTally->rank) {
                        dump("Actual poll tally", $actual);
                        $this->failTrans('proposal_rank_mismatch', [
                            'expected_rank' => $expected[$proposalUuid][$rankAtom],
                            'actual_rank' => $proposalTally->rank,
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


    /**
     * @Then /^(?P<actor>.+?) devr(?:ai[st]|aient|ions)(?: encore| aussi)? (?:(?P<ok>réussir)|(?P<ko>échouer))$/u
     * @Then /^(?P<actor>.+?) should (?:(?P<ok>succeed)|(?P<ko>fail))$/
     */
    public function actorShouldSucceedOrFail($actor, $ok=null, $ko=null)
    {
        $tx = $this->actor($actor)->getLastTransaction();

        if (empty($ko)) {
            $this->actor($actor)->assertTransactionSuccess($tx);
        } else if (empty($ok)) {
            $this->actor($actor)->assertTransactionFailure($tx);
        } else {
            $this->fail("Bad Regex?");
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    // …

}
