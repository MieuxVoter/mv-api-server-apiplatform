<?php

/**
 * Annotations for linters, code inspectors, etc.
 * @noinspection PhpDocSignatureInspection
 */


use App\Entity\LimajuPoll;
use App\Entity\LimajuPollOption;
use App\Entity\User;


/**
 * This context class contains definitions of some of the more general-purpose steps.
 *
 * This is also a blackboard for new step defs, until we figure out where to store them.
 */
class MainFeatureContext extends BaseFeatureContext
{

    /**
     * @Given a dummy authentication desk that validates any AAT
     * @Given /^un bureau d'authentification un peu simplet qui validerait n'importe quel (?:JAA|jeton)$/iu
     */
    public function givenDummyAuthenticationDesk()
    {
        // todo
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @Given /^a citizen named (?P<name>.+)$/ui
     * @Given /^un(?:⋅?e)? (?:utilisat(?:eure?|rice)|citoyen(?:⋅?ne)?)(?: .*)? (?:sur)?nommé(?:⋅?e)? (?P<name>.+)$/ui
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
     * @Given /^un scrutin au jugement majoritaire comme suit:$/u
     * @Given /^a majority judgment poll like so:$/u
     */
    public function givenLimajuPoll($pystring)
    {
        $titleKey = $this->t('keys.poll.title');
        $optionsKey = $this->t('keys.poll.options');
        $data = $this->yaml($pystring);

        $poll = new LimajuPoll();

        if ( ! isset($data[$titleKey])) {
            $this->fail("Set poll title with '${titleKey}:'.");
        }

        $poll->setTitle($data[$titleKey]);
        $this->persist($poll);

        if ( ! isset($data[$optionsKey])) {
            $this->fail("At least one option is required in '${optionsKey}:'.");
        }

        foreach ($data[$optionsKey] as $optionTitle) {
            $option = new LimajuPollOption();
            $option->setTitle($optionTitle);
            $poll->addOption($option);
            $this->persist($option);
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
     * @Then /^(?:qu')?il(?: ne)? d(?:oi|evrai)t(?: maintenant)? y avoir (?P<thatMuch>.+) scrutins? au jugement majoritaire dans la base de données$/ui
     */
    public function thereShouldBeSomeLimajuPollsInTheDatabase($thatMuch)
    {
        $this->thereShouldBeExactlyThatMuchEntitiesInTheDatabase($thatMuch, LimajuPoll::class);
    }



    /**
     * fixme: en step
     * Then /^there should(?: now)?(?: still)?(?: only)? be (?P<thatMuch>.+) majority judgment polls? in the database$/ui
     * @Then /^(?:que?' ?)?(?P<actor>.+?)(?: ne)? d(?:oi|evrai)t(?: maintenant)? avoir (?P<thatMuch>.+) votes? sur le scrutin(?: au jugement majoritaire)? titré "(?P<title>.+?)"$/ui
     */
    public function actorShouldHaveSomeLimajuPollOptionVotesForPoll($actor, $thatMuch, $title)
    {
        $actor = $this->actor($actor);
        $thatMuch = $this->number($thatMuch);
        $poll = $this->findOneLimajuPollFromTitle($title);
        // fixme: for poll

        $votes = $this->getLimajuPollOptionVoteRepository()->findBy([
            'author' => $actor->getUser()->getId(),
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
    public function theTallyOfTheLimajuPollTitledShouldBeLikeYaml($tally, $title, $pystring)
    {
        $expectedRaw = $this->yaml($pystring);
        $poll = $this->findOneLimajuPollFromTitle($title);

        $mentionAtom = 'mention';
        $positionAtom = 'position';

        $expected = [];
        $pollOptions = [];
        foreach ($expectedRaw as $optionTitle => $localizedMentionOrData) {
            $pollOption = $this->findOneLimajuPollOptionFromTitleAndPoll($optionTitle, $poll);
            $pollOptionId = $pollOption->getId()->toString();
            $pollOptions[$pollOptionId] = $pollOption;

            if ( ! is_array($localizedMentionOrData)) {
                $localizedMentionOrData = [
                    $mentionAtom => $localizedMentionOrData,
                ];
            }

            $localizedMentionOrData[$mentionAtom] = $this->unlocalizeLimajuPollMention($localizedMentionOrData[$mentionAtom]);
            $expected[$pollOptionId] = $localizedMentionOrData;
        }

        $tallyBot = $this->getTallyBot($tally);
        $actual = $tallyBot->tallyVotesOnLimajuPoll($poll);

        $assertedSomething = false;
        $expectationsLeftToProcess = array_keys($expected);

        foreach ($actual->options as $optionTally) {

            $optionId = $optionTally->poll_option_id->toString();

            if (isset($expected[$optionId])) {
                $assertedSomething = true;
                $expectationsLeftToProcess = array_diff($expectationsLeftToProcess, [$optionId]);

                $pollOption = $this->findOneLimajuPollOptionFromId($optionId);
                if ($expected[$optionId][$mentionAtom] !== $optionTally->mention) {
                    dump("Actual option tally", $optionTally);
                    $this->failTrans("option_tallies_dont_match", [
                        'expected_mention' => $this->t('majority_judgment_poll.mention.'.$expected[$optionId][$mentionAtom]),
                        'actual_mention' => $this->t('majority_judgment_poll.mention.'.$optionTally->mention),
                        'option' => $pollOption,
                    ]);
                }

                if (isset($expected[$optionId][$positionAtom])) {
                    if ($expected[$optionId][$positionAtom] !== $optionTally->position) {
                        dump("Actual poll tally", $actual);
                        $this->failTrans('option_position_mismatch', [
                            'expected_position' => $expected[$optionId][$positionAtom],
                            'actual_position' => $optionTally->position,
                            'option' => $pollOption,
                        ]);
                    }
                }

            }
        }

        if (0 < count($expectationsLeftToProcess)) {
            $optionsLeft = array_map(function($e) use ($pollOptions) {
                return $pollOptions[$e];
            }, $expectationsLeftToProcess);
            $this->failTrans("options_left_unprocessed", [
                'expected' => $expected,
                'options' => $optionsLeft,
            ]);
        }

        if ( ! $assertedSomething) {
            $this->fail("You did not assert anything in this step?");
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @Then /^(?P<actor>.+?) should (?:(?P<ok>succeed)|(?P<ko>fail))$/
     * @Then /^(?P<actor>.+?) devr(?:ai[st]|aient|ions) (?:(?P<ok>réussir)|(?P<ko>échouer))$/u
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
