<?php

/**
 * Annotations for linters, code inspectors, etc.
 * @noinspection PhpDocSignatureInspection
 */


use App\Entity\Poll;
use App\Entity\PollProposal;
use App\Entity\User;


/**
 * This context class contains definitions of some of the more general-purpose steps.
 *
 * This is also a blackboard for new step defs, until we figure out where to store them.
 */
class MainFeatureContext extends BaseFeatureContext
{

    /**
     * @Given /^a citizen named (?P<name>.+)$/ui
     * @Given /^un(?:⋅?e)? (?:utilisat(?:eure?|rice)|élect(?:eure?|rice)|citoyen(?:⋅?ne)?)(?: .*)? (?:sur)?nommé(?:⋅?e)? (?P<name>.+)$/ui
     */
    public function givenCitizenNamed($name)
    {
        $userAndToken = $this->createUser($name);

        $actor = $this->actor($name, true);
        $actor->setUser($userAndToken['user']);
        $actor->setPassword($userAndToken['token']);
    }


    /**
     * @Given /^(?P<actor>.+?) (?:am|is|are) (?:a|the) citizen named (?P<name>.+)$/u
     * @Given /^(?P<actor>.+?) (?:suis|est?) un(?:⋅?e)? citoyen(?:⋅?ne)? nommé(?:⋅?e)? (?P<name>.+)$/u
     */
    public function givenActorIsCitizenNamed($actor, $name)
    {
        $userAndToken = $this->createUser($name);

        $actor = $this->actor($actor, true);
        $actor->setUser($userAndToken['user']);
        $actor->setPassword($userAndToken['token']);
    }


    /**
     * @Given /^a moderator named (?P<name>.+)$/ui
     * @Given /^un(?:⋅?e)? modérat(?:eur[⋅.]?e?|rice)(?: .*?)? (?:sur)?nommé(?:⋅?e)? (?P<name>.+)$/ui
     */
    public function givenModeratorNamed($name)
    {
        $userAndToken = $this->createUser($name);

        $actor = $this->actor($name, true);
        $actor->setUser($userAndToken['user']);
        $actor->setPassword($userAndToken['token']);

        $userId = $actor->getUser()->getId();
        $roleName = 'ROLE_ADMIN';
        $context = [];
        $this->app()->getMessageBus()->dispatch(new AddUserRole($userId, $roleName, $context));
    }


    /**
     * @Given /^un scrutin(?: au jugement majoritaire)? comme suit:?$/ui
     * @Given /^a majority judgment poll like so:?$/ui
     */
    public function givenLimajuPoll($pystring)
    {
        $subjectKey = $this->t('keys.poll.subject');
        $proposalsKey = $this->t('keys.poll.proposals');
        $data = $this->yaml($pystring);

        $poll = new Poll();

        if ( ! isset($data[$subjectKey])) {
            $this->failTrans("poll_has_no_subject");
        }

        $poll->setSubject($data[$subjectKey]);
        $this->persist($poll);

        if ( ! isset($data[$proposalsKey])) {
            $this->fail("At least one proposal is required in '${proposalsKey}:'.");
        }

        foreach ($data[$proposalsKey] as $candidateTitle) {
            $proposal = new PollProposal();
            $proposal->setTitle($candidateTitle);
            $poll->addProposal($proposal);
            $this->persist($proposal);
        }

        $this->flush();
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// ASSERTIONS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) users? in the database$/u
     * @Then /^(?:qu')?il(?: ne)? d(?:oi|evrai)t(?: maintenant)? y avoir (?P<thatMuch>.+) utilisateur(?:⋅?e)?s? dans la base de données$/ui
     */
    public function thereShouldBeSomeUsersInTheDatabase($thatMuch)
    {
        $this->thereShouldBeExactlyThatMuchEntitiesInTheDatabase($thatMuch, User::class);
    }


    /**
     * @Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) majority judgment polls? in the database$/ui
     * @Then /^(?:qu')?il(?: ne)? d(?:oi|evrai)t(?: maintenant)?(?: encore)? y avoir (?P<thatMuch>.+) scrutins?(?: au jugement majoritaire)? dans la base de données$/ui
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
     * Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) majority judgment polls? in the database$/ui
     * @Then /^(?:que?' ?)?(?P<actor>.+?)(?: ne)? d(?:oi|evrai)t(?: maintenant)?(?: encore)? avoir (?P<thatMuch>.+) votes? sur le scrutin(?: au jugement majoritaire)? titré "(?P<title>.+?)"$/ui
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
     * fixme: en step
     * @Then /^le dépouillement(?: de)? (?P<tally>standard) du scrutin au jugement majoritaire titré "(?P<title>.*)" devrait être *:?$/u
     */
    public function theTallyOfThePollTitledShouldBeLikeYaml($tally, $title, $pystring)
    {
        $expectedRaw = $this->yaml($pystring);
        $poll = $this->findOnePollFromSubject($title);

        $mentionAtom = 'mention';
        $positionAtom = 'position';

        $expected = [];
        $PollProposals = [];
        foreach ($expectedRaw as $candidateTitle => $localizedMentionOrData) {
            $PollProposal = $this->findOnePollProposalFromTitleAndPoll($candidateTitle, $poll);
            $PollProposalId = $PollProposal->getId()->toString();
            $PollProposals[$PollProposalId] = $PollProposal;

            if ( ! is_array($localizedMentionOrData)) {
                $localizedMentionOrData = [
                    $mentionAtom => $localizedMentionOrData,
                ];
            }

            $localizedMentionOrData[$mentionAtom] = $this->unlocalizePollMention($localizedMentionOrData[$mentionAtom]);
            $expected[$PollProposalId] = $localizedMentionOrData;
        }

        $tallyBot = $this->getTallyBot($tally);
        $actual = $tallyBot->tallyVotesOnLimajuPoll($poll);

        $assertedSomething = false;
        $expectationsLeftToProcess = array_keys($expected);

        foreach ($actual->candidates as $candidateTally) {

            $candidateId = $candidateTally->poll_candidate_id->toString();

            if (isset($expected[$candidateId])) {
                $assertedSomething = true;
                $expectationsLeftToProcess = array_diff($expectationsLeftToProcess, [$candidateId]);

                $PollProposal = $this->findOneLimajuPollProposalFromId($candidateId);
                if ($expected[$candidateId][$mentionAtom] !== $candidateTally->mention) {
                    dump("Actual candidate tally", $candidateTally);
                    $this->failTrans("candidate_tallies_dont_match", [
                        'expected_mention' => $this->t('majority_judgment_poll.mention.'.$expected[$candidateId][$mentionAtom]),
                        'actual_mention' => $this->t('majority_judgment_poll.mention.'.$candidateTally->mention),
                        'candidate' => $PollProposal,
                    ]);
                }

                if (isset($expected[$candidateId][$positionAtom])) {
                    if ($expected[$candidateId][$positionAtom] !== $candidateTally->position) {
                        dump("Actual poll tally", $actual);
                        $this->failTrans('candidate_position_mismatch', [
                            'expected_position' => $expected[$candidateId][$positionAtom],
                            'actual_position' => $candidateTally->position,
                            'candidate' => $PollProposal,
                        ]);
                    }
                }

            }
        }

        if (0 < count($expectationsLeftToProcess)) {
            $candidatesLeft = array_map(function($e) use ($PollProposals) {
                return $PollProposals[$e];
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
     * @Then /^(?P<actor>.+?) should (?:(?P<ok>succeed)|(?P<ko>fail))$/
     * @Then /^(?P<actor>.+?) devr(?:ai[st]|aient|ions)(?: encore| aussi)? (?:(?P<ok>réussir)|(?P<ko>échouer))$/u
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
