<?php  /** @noinspection PhpDocSignatureInspection */


/**
 *
 *
 * *WE ARE NOT USING THIS ANYMORE*
 *
 *
 * We had a homemade implementation of GraphQL support at first.
 * We removed it because…  Well…  ApiPlatform is promising, and is the right thing to do.
 * We are now waiting for API Platform to be mature enough to support GraphQL sanely.
 *
 * Steps using the GraphQL form of the API.
 *
 * This file is linked to ApiRestFeatureContext and should define the same steps, but differently.
 */
class ApiGraphFeatureContext extends BaseFeatureContext
{

    /**
     * @When /^(?P<actor>.+) soumets? l[ea] (?:travail|proposition) suivante? *:$/u
     * @When /^(?P<actor>.+) submits? the following work *:$/
     */
    public function actorSubmitsTheFollowingWork($actor, $pystring)
    {
        $work = $this->yaml($pystring);

        $query = <<<'QUERY'
mutation workWithTitleOnly($title: String!) {
  createWork(work: {title: $title}) {
    title
  }
}
QUERY;

        $variables = [
            'work' => $work, // test
            'title' => $work['title'],
        ];

        $this->actor($actor)->gql($query, $variables);
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> *essa[iy]e de)? vot(?:e[szr]?|ons|ent)(?: finalement)? (?:(?P<for>POUR)|(?P<against>CONTRE)) la proposition titrée "(?P<title>.+)"$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? votes? (?:(?P<for>FOR)|(?P<against>AGAINST)) the work titled "(?P<title>.+)"$/
     */
    public function actorVotesOnTheWorkTitled($actor, $try, $for, $against, $title)
    {
        $query = <<<'QUERY'
mutation voteForOrAgainst($workId: ID!, $vote: NewVote!) {
  voteOnWork(workId: $workId, vote: $vote) {
    title
    createdAt
    updatedAt
  }
}
QUERY;

        $vote = null;
        if ( ! empty($for)) $vote = 'AYE';
        if ( ! empty($against)) $vote = 'NAY';
        $vote = ['decision' => $vote];

        $work = $this->findOneWorkFromTitle($title);
        $workId = $work->getId();

        $variables = [
            'workId' => $workId,
            'vote' => $vote,
        ];

        $this->actor($actor)->gql($query, $variables, !empty($try));
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> *essa[iy]ez? de)? dél[éè]gu(?:e[szr]?|ons|ent)(?: finalement)? ses votes à (?P<delegate>.+)$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? delegate? (?:my|her|his|their) votes to (?P<delegate>.+)$/ui
     */
    public function actorDelegatesToCitizen($actor, $try, $delegate)
    {
        $subordinate = $this->user($delegate);

        $query = <<<'QUERY'
mutation delegateToCitizen($subordinateId: String!) {
  delegateToCitizen(subordinateId: $subordinateId) {
    elector { id }
    subordinate { id }
  }
}
QUERY;

        $variables = [ 'subordinateId' => $subordinate->getId() ];

        $this->actor($actor)->gql($query, $variables, !empty($try));
    }


    /**
     * This step makes multiple HTTP queries, one per poll candidate.
     * Use:
     *     Then <actor> prints the last <count> transactions
     *
     * @When /^(?P<actor>.+?)(?P<try> *essa[iy]ez? de)? vot(?:e[szr]?|ent) sur le scrutin au jugement majoritaire titré "(?P<title>.+)" *:$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? votes? on the majority judgment poll titled "(?P<title>.+)" *:$/ui
     */
    public function actorVotesOnTheLimajuPollTitled($actor, $try, $title, $pystring)
    {
        $data = $this->yaml($pystring);

        $poll = $this->findOneLimajuPollFromSubject($title);
        $pollId = $poll->getId();

        $opinions = [];
        foreach ($data as $candidateTitle => $localizedMention) {
            $pollCandidate = $this->findOneLimajuPollCandidateFromTitleAndPoll($candidateTitle, $poll);
            $mention = $this->unlocalizeLimajuPollMention($localizedMention);
            $opinions[(string)$pollCandidate->getId()] = $mention;

            $query = <<<'QUERY'
mutation voteOnLimajuPoll($candidateIri: String!, $mention: String!) {
    createLimajuPollCandidateVote(input: { candidate: $candidateIri, mention: $mention }) {
        PollCandidateVote { id, author { id } }
    }
}
QUERY;
            $variables = array(
                'candidateIri' => $this->iri($pollCandidate),
                'mention' => $mention,
            );
            $this->actor($actor)->gqlNew($query, $variables, !empty($try));
        }

    }



    /**
     * This step is way too long.
     * - we're doing multiple requests (help wanted with graphql denormalization context)
     * - the YAML parsing business could be refactored, since we use it in REST as well.
     *
     * @When /^(?P<actor>.+?)(?P<try> *essa[iy]ez? de|) cré(?:e[szr]?|ent) (?:le|un) scrutin au jugement majoritaire (?:comme suit|suivant) *:$/u
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to|) creates? the following majority judgment poll:$/ui
     */
    public function actorSubmitsTheLimajuPollLikeSo($actor, $try, $pystring)
    {
        $data = $this->yaml($pystring);

        $candidates = [];
        if (isset($data[$this->t('keys.poll.candidates')])) {
            foreach ($data[$this->t('keys.poll.candidates')] as $candidateDatum) {
                if (is_string($candidateDatum)) {
                    $candidateDatum = ['title' => $candidateDatum];
                }
                $candidates[] = $candidateDatum;
            }
        }

        $query = <<<'QUERY'
mutation createLimajuPoll($input: createLimajuPollInput!) {
    createLimajuPoll(input: $input) {
        Poll {
            id
            title
        }
    }
}
QUERY;

        $variables = [
            'input' => [
                'title' => $data[$this->t('keys.poll.title')],
//                'candidates' => $candidates,
            ],
        ];
        $tx = $this->actor($actor)->gqlNew($query, $variables, !empty($try));

        // Since we can't manage to post candidates as well in one single request,
        // even though we configured the graphql denormalization just like REST.
        // Maybe we're missing something… Time will tell.
        // Anyhow, we're posting the candidates afterwards, and we use the returned poll's IRI.
        $response = json_decode($tx->getResponse()->getContent());
        $pollIri = $response->data->createLimajuPoll->LimajuPoll->id;

        foreach ($candidates as $candidate) {
            $query = <<<'QUERY'
mutation createLimajuPollCandidate($input: createLimajuPollCandidateInput!) {
    createLimajuPollCandidate(input: $input) {
        PollCandidate {
            id
            title
        }
    }
}
QUERY;
            $variables = [
                'input' => [
                    'title' => $candidate['title'],
                    'poll' => $pollIri,
                ],
            ];
            $this->actor($actor)->gqlNew($query, $variables, !empty($try));
        }

    }

}
