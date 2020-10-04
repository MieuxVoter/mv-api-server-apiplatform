<?php /** @noinspection PhpDocSignatureInspection */


/**
 * Steps using the REST form of the API.
 *
 * This file is linked to ApiGraphFeatureContext and should define the same steps, but differently.
 * This is because this file is part of the "default" test-suite but not part of the "graphql" test-suite.
 */
class ApiRestFeatureContext extends BaseFeatureContext
{

    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) créer? un compte utilisateur avec le courriel "(?P<email>.*)" et le mot de passe "(?P<password>.*)"$/ui
     */
    public function actorRegistersWithEmailAndPassword($actor, $try, $email, $password)
    {
        $this->actor($actor, true)->api(
            'POST',"/users",
            [
                'email' => $email,
                'password' => $password,
            ], [], !empty($try)
        );
    }

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
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) vot(?:e[szr]?|ent) sur le scrutin(?: au jugement majoritaire)? (?:titré|intitulé|assujettissant) "(?P<pollSubject>.+)" *:$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? votes? on the majority judgment poll titled "(?P<pollSubject>.+)" *:$/ui
     */
    public function actorVotesOnThePollTitled($actor, $try, $pollSubject, $pystring)
    {
        $poll = $this->findOnePollFromSubject($pollSubject);
        $data = $this->yaml($pystring);

        foreach ($data as $proposalTitle => $mentionTitle) {
            $proposal = $this->findOnePollProposalFromTitleAndPoll($proposalTitle, $poll);
            $pollId = $poll->getUuid();
            $proposalId = $proposal->getUuid();
            $this->actor($actor)->api(
                'POST',"/polls/{$pollId}/proposals/{$proposalId}/vote",
                [
                    // the author is inferred from auth
//                    'author' => $this->iri($this->actor($actor)->getUser()),
                    'mention' => $mentionTitle,
                ], [], !empty($try)
            );
        }
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) cré(?:e[szr]?|ent) (?:le|un) scrutin au jugement majoritaire (?:comme suit|suivant) *:$/u
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to|) creates? the following majority judgment poll:$/ui
     */
    public function actorSubmitsTheLimajuPollLikeSo($actor, $try, $pystring)
    {
        $data = $this->yaml($pystring);

        $candidates = [];
        if (isset($data[$this->t('keys.poll.proposals')])) {
            foreach ($data[$this->t('keys.poll.proposals')] as $proposal) {
                if (is_string($proposal)) {
                    $proposal = ['title' => $proposal];
                }
                $candidates[] = $proposal;
            }
        }

        $this->actor($actor)->api(
            'POST',"/polls",
            [
                'subject' => $data[$this->t('keys.poll.subject')],
                'proposals' => $candidates,
            ], [], !empty($try)
        );
    }


    public $that_tally;

    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) dépouill(?:e[szr]?|ent)(?: (?:de|à) nouveau)? le scrutin titré "(?P<title>.+)"$/ui
     * When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? tall(?:y|ies) the majority judgment poll titled "(?P<title>.+)"$/ui
     */
    public function actorTalliesTheLimajuPollTitled($actor, $try, $title)
    {
        $poll = $this->findOnePollFromSubject($title);

        $tx = $this->actor($actor)->api(
            'GET',"/poll_tally/".$poll->getId(),
            [], [], !empty($try)
        );

        if ($tx->getResponse()->isSuccessful()) {
            $this->that_tally = json_decode($tx->getResponse()->getContent());
        } else {
            $this->that_tally = null;
        }
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) supprimer?(?: (?:de|à) nouveau)? le scrutin titré "(?P<title>.+)"$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to|) deletes? the majority judgment poll titled "(?P<title>.+)"$/ui
     */
    public function actorDeletesTheLimajuPollTitled($actor, $try, $title)
    {
        $poll = $this->findOnePollFromSubject($title);

        $this->actor($actor)->api(
            'DELETE',"/polls/".$poll->getId(),
            [], [], !empty($try)
        );
    }

}
