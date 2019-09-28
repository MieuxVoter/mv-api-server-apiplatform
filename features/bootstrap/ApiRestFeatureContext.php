<?php /** @noinspection PhpDocSignatureInspection */


/**
 * Steps using the REST form of the API.
 *
 * This file is linked to ApiGraphFeatureContext and should define the same steps, but differently.
 * This is because this file is part of the "default" test-suite but not part of the "graphql" test-suite.
 */
class ApiRestFeatureContext extends BaseFeatureContext
{

//    /**
//     * @When /^(?P<actor>.+?)(?P<try> *essa[iy]ez? de)? dél[éè]gu(?:e[szr]?|ons|ent)(?: finalement)? ses votes à (?P<delegate>.+)$/ui
//     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? delegate? (?:my|her|his|their) votes to (?P<delegate>.+)$/ui
//     */
//    public function actorDelegatesToCitizen($actor, $try, $delegate)
//    {
//        $subordinate = $this->user($delegate);
//
//        $parameters = [ 'subordinate' => $subordinate->getId() ];
//
//        $this->actor($actor)->apiOld('POST', "/delegation", $parameters, !empty($try));
//    }


    /**
     * This step makes multiple requests, one per given mention.
     *
     * @When /^(?P<actor>.+?)(?P<try> *essa[iy]ez? de)? vot(?:e[szr]?|ent) sur le scrutin au jugement majoritaire titré "(?P<title>.+)" *:$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? votes? on the majority judgment poll titled "(?P<title>.+)" *:$/ui
     */
    public function actorVotesOnTheLimajuPollTitled($actor, $try, $title, $pystring)
    {
        $poll = $this->findOneLimajuPollFromTitle($title);
        $data = $this->yaml($pystring);

        foreach ($data as $optionTitle => $localizedMention) {
            $pollOption = $this->findOneLimajuPollOptionFromTitleAndPoll($optionTitle, $poll);
            $mention = $this->unlocalizeLimajuPollMention($localizedMention);
            $this->actor($actor)->api(
                'POST',"/limaju_poll_option_votes",
                [
                    // the author is inferred from auth
//                    'author' => $this->iri($this->actor($actor)->getUser()),
                    'option' => $this->iri($pollOption),
                    'mention' => $mention,
                ], [], !empty($try)
            );
        }
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> *essa[iy]ez? de|) cré(?:e[szr]?|ent) (?:le|un) scrutin au jugement majoritaire (?:comme suit|suivant) *:$/u
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to|) creates? the following majority judgment poll:$/ui
     */
    public function actorSubmitsTheLimajuPollLikeSo($actor, $try, $pystring)
    {
        $data = $this->yaml($pystring);

        $options = [];
        if (isset($data[$this->t('keys.poll.options')])) {
            foreach ($data[$this->t('keys.poll.options')] as $optionDatum) {
                if (is_string($optionDatum)) {
                    $optionDatum = ['title' => $optionDatum];
                }
                $options[] = $optionDatum;
            }
        }

        $this->actor($actor)->api(
            'POST',"/limaju_polls",
            [
                'title' => $data[$this->t('keys.poll.title')],
                'options' => $options,
            ], [], !empty($try)
        );
    }

}
