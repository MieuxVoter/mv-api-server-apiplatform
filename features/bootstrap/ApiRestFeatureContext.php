<?php /** @noinspection PhpDocSignatureInspection */

namespace Features;

use Exception;
use Features\Steps\BasicGetTrait;
use Features\Steps\DomCrawlerSteps;
use Features\Steps\ResponseAnalysisSteps;


/**
 * Steps using the REST form of the API.
 *
 * This file is linked to ApiGraphFeatureContext and should define the same steps, but differently.
 * This is because this file is part of the "default" test-suite but not part of the "graphql" test-suite.
 */
class ApiRestFeatureContext extends BaseFeatureContext
{

    use DomCrawlerSteps;
    use BasicGetTrait;
    use ResponseAnalysisSteps;

    // Let's try and use traits from now on for generic steps.
    // Registration below perhaps ought to be refactored in a Trait as well.

    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) créer? un compte utilisat(?:rice|eur(?:⋅?e)?) (?:ainsi|comme suit) *:?$/ui
     */
    public function actorRegistersLikeSo($actor, $try, $pystring)
    {
        $data = $this->yaml($pystring);
        $this->actor($actor, true)->api(
            'POST', "/users",
            $data, [], !empty($try)
        );
    }

    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) créer? un compte utilisat(?:rice|eur(?:⋅?e)?)s? avec le mot de passe "(?P<password>.*)"$/ui
     */
    public function actorRegistersWithPassword($actor, $try, $password)
    {
        $this->actor($actor, true)->api(
            'POST', "/users",
            [
                'username' => $actor,
                'password' => $password,
            ], [], !empty($try)
        );
    }

    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) créer? un compte utilisat(?:rice|eur(?:⋅?e)?)s? avec le courriel "(?P<email>.*)" et le mot de passe "(?P<password>.*)"$/ui
     */
    public function actorRegistersWithEmailAndPassword($actor, $try, $email, $password)
    {
        $this->actor($actor, true)->api(
            'POST', "/users",
            [
                'username' => $actor,
                'email' => $email,
                'password' => $password,
            ], [], !empty($try)
        );
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tentez?) d'|)obt(?:enir|ien[st]) un jeton avec le mot de passe "(?P<password>.*)"$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(:?y|ies)|) to get an authentication token using password "(?P<password>.*)"$/ui
     */
    public function actorRequestsAuthenticationTokenWithPassword($actor, $try, $password)
    {

        $this->actor($actor, true)->api(
            'POST', "/_jwt",
            [
                'usernameOrEmail' => $actor,
//                'email' => $email,
                'password' => $password,
            ], [], !empty($try)
        );
    }

    /**
     * This step makes multiple requests, one per given mention.
     *
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) vot(?:e[szr]?|ent) sur le scrutin(?: au jugement majoritaire)? (?:sur|pour|de|titré|intitulé|assujetti(?:ssant)?) "(?P<pollSubject>.+)" *:$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? votes? on the majority judgment poll titled "(?P<pollSubject>.+)" *:$/ui
     */
    public function actorVotesOnThePollOnTheSubjectOf($actor, $try, $pollSubject, $pystring)
    {
        $poll = $this->findOnePollFromSubject($pollSubject);
        $data = $this->yaml($pystring);

        foreach ($data as $proposalTitle => $gradeName) {
            $proposal = $this->findOneProposalFromTitleAndPoll($proposalTitle, $poll);
            $grade = $this->getGradeRepository()->findOneByPollAndName($poll, $gradeName);
            if (null === $grade) {
                $this->failTrans("no_grade_matching_name", ['name' => $gradeName]);
            }
            $pollId = $poll->getUuid();
            $proposalId = $proposal->getUuid();
            $this->actor($actor)->api(
                'POST', "/polls/{$pollId}/proposals/{$proposalId}/ballots",
                [
                    // the author is inferred from auth
//                    'author' => $this->iri($this->actor($actor)->getUser()),
                    'grade' => $this->iri($grade),
                ], [], !empty($try)
            );
        }
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) cré(?:e[szr]?|ent) (?:le|un) scrutin(?: au jugement majoritaire)? (?:comme suit|suivant) *:?$/u
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to|) creates? the following majority judgment poll:$/ui
     */
    public function actorCreatesThePollLikeSo($actor, $try, $pystring)
    {
        $data = $this->yaml($pystring);

        $proposals = [];
        if (isset($data[$this->t('keys.poll.proposals')])) {
            foreach ($data[$this->t('keys.poll.proposals')] as $proposal) {
                if (is_string($proposal)) {
                    $proposal = ['title' => $proposal];
                }
                $proposals[] = $proposal;
            }
        }

        $grades = [];
        if (isset($data[$this->t('keys.poll.grades')])) {
            foreach ($data[$this->t('keys.poll.grades')] as $k => $grade) {
                if (is_string($grade)) {
                    $grade = [
                        'name' => $grade,
                        'level' => $k,
                    ];
                }
                $grades[] = $grade;
            }
        }

        $extraContent = [];
        if (isset($data[$this->t('keys.poll.scope')])) {
            $extraContent['scope'] = $this->t('values.poll.scope.' . $data[$this->t('keys.poll.scope')]);
        }

        $this->actor($actor)->api(
            'POST', "/polls",
            [
                'subject' => $data[$this->t('keys.poll.subject')],
                'proposals' => $proposals,
                'grades' => $grades,
            ] + $extraContent, [], !empty($try)
        );
    }


    public $that_tally;
    public $that_tally_poll;

    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) dépouill(?:e[szr]?|ent)(?: (?:de|à) nouveau)? le scrutin (?:titré|intitulé|assujettissant|au sujet d[eu']s?) *"(?P<title>.+)"$/ui
     * When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? tall(?:y|ies) the majority judgment poll titled "(?P<title>.+)"$/ui
     */
    public function actorTalliesThePollTitled($actor, $try, $title)
    {
        $poll = $this->findOnePollFromSubject($title);

        $tx = $this->actor($actor)->api(
            'GET', "/polls/" . $poll->getUuid() . "/result",
            [], [], !empty($try)
        );

        if ($tx->getResponse()->isSuccessful()) {
            $this->that_tally = $tx->getResponseJson();
            $this->that_tally_poll = $poll;
        } else {
            $this->that_tally = null;
            $this->that_tally_poll = null;
        }
    }

    /**
     * @Then /^ce dépouillement devrait être ?:?$/iu
     * @Then /^that tally should be ?:?$/iu
     */
    public function thatTallyShouldBe($pystring)
    {
        if (null == $this->that_tally) {
            $this->failTrans("that_tally_undefined");
        }
//        dump($this->that_tally);
//        array:7 [
//          "@context" => "/api/contexts/Tally"
//          "@id" => "/api/polls/1b62a229-1ac0-40bb-a824-ea6ae0f3fae3/tally"
//          "@type" => "Tally"
//          "id" => "1b62a229-1ac0-40bb-a824-ea6ae0f3fae3"
//          "poll" => "/api/polls/37df0b4e-1ba2-4aef-9c5d-415861f7579e"
//          "algorithm" => "standard"
//          "leaderboard" => array:4 [
//            0 => array:2 [
//              "proposal" => array:4 [
//                "@id" => "/api/proposals/3c4f5288-7e40-4b57-9b57-be40e8475a0c"
//                "@type" => "Proposal"
//                "uuid" => "3c4f5288-7e40-4b57-9b57-be40e8475a0c"
//                "title" => "Épisode IV"
//              ]
//              "rank" => 1
//              "medianGrade": {#49504
//                "@id": "/api/grades/085a0cf6-409d-47fd-b553-ba3f84840d97"
//                "@type": "Grade"
//                "uuid": "085a0cf6-409d-47fd-b553-ba3f84840d97"
//                "name": "très bien"
//                "level": 5
//              }
//            ]

        $data = $this->yaml($pystring);

        foreach ($data as $proposalTitle => $datum) {
            $rank = $datum[$this->t('keys.proposal.rank')];
            $grade = $datum[$this->t('keys.proposal.grade')];

//            $proposal = $this->getProposalRepository()->findOneBy([
//                'title' => $proposalTitle,
//                'poll' => $this->that_tally_poll,
//            ]);
            $proposal = $this->findOneProposalFromTitleAndPoll($proposalTitle, $this->that_tally_poll);

            $found = false;
            foreach ($this->that_tally['leaderboard'] as $actualProposalTally) {
                if ($actualProposalTally['proposal']['title'] !== $proposalTitle) {
                    continue;
                }
                $found = true;
                if ($rank !== $actualProposalTally['rank']) {
                    $this->failTrans("proposal_rank_mismatch", [
                        'proposal' => $proposal,
                        'expected_rank' => $rank,
                        'actual_rank' => $actualProposalTally['rank'],
                    ]);
                }
                if ($grade !== $actualProposalTally['medianGrade']['name']) {
                    $this->failTrans("proposal_median_grade_mismatch", [
                        'proposal' => $proposal,
                        'expected_grade' => $grade,
                        'actual_grade' => $actualProposalTally['medianGrade']['name'],
                    ]);
                }


            }

            if ( ! $found) {
                $this->failTrans('no_proposal_matching_title', [
                    'title' => $proposalTitle,
                ]);
            }
        }

    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) supprimer?(?: (?:de|à) nouveau)? le scrutin (?:titré|intitulé|assujettissant) "(?P<title>.+)"$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to|) deletes? the majority judgment poll titled "(?P<title>.+)"$/ui
     */
    public function actorDeletesThePollTitled($actor, $try, $title)
    {
        $poll = $this->findOnePollFromSubject($title);

        $this->actor($actor)->api(
            'DELETE', "/polls/" . $poll->getUuid(),
            [], [], !empty($try)
        );
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) g[ée]n[éèe]r(?:e[szr]?|ent) (?P<invitationsAmount>.+?) invitations? pour le scrutin(?: au jugement majoritaire)? (?:sur|pour|de|titré|intitulé|assujetti(?:ssant)?) "(?P<pollSubject>.+)"$/ui
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to|) generate (?P<invitationsAmount>.+?) invitations? on the poll titled "(?P<pollSubject>.+)"$/ui
     * @throws Exception
     */
    public function actorGeneratesInvitationsForPollAbout($actor, $try, $invitationsAmount, $pollSubject)
    {
        $actor = $this->actor($actor);
        $invitationsAmount = $this->number($invitationsAmount);
        $poll = $this->findOnePollFromSubject($pollSubject);
        $pollId = $poll->getUuid();

        $actor->api(
            'GET', "/polls/{$pollId}/invitations",
            [], [
            'limit' => $invitationsAmount,
        ], !empty($try)
        );

        $content = $actor->getLastTransaction()->getResponseJson();
        //dump($content);
        //array:6 [
        //  "@context" => "/api/contexts/Invitation"
        //  "@id" => "/api/invitations"
        //  "@type" => "hydra:Collection"
        //  "hydra:member" => array:2 [
        //    0 => array:4 [
        //      "@id" => "/api/invitations/14c8e709-47a1-4451-ac2e-6f73f13d21c5"
        //      "@type" => "Invitation"
        //      "uuid" => "14c8e709-47a1-4451-ac2e-6f73f13d21c5"
        //      "poll" => "/api/polls/e1517447-9758-4a79-b723-9c4b993af521"
        //    ]
        //    1 => array:4 [
        //      "@id" => "/api/invitations/8442cb74-0337-4232-a5d7-5acf3a5086a9"
        //      "@type" => "Invitation"
        //      "uuid" => "8442cb74-0337-4232-a5d7-5acf3a5086a9"
        //      "poll" => "/api/polls/e1517447-9758-4a79-b723-9c4b993af521"
        //    ]
        //  ]
        //  "hydra:totalItems" => 2
        //  "hydra:view" => array:2 [
        //    "@id" => "/api/polls/e1517447-9758-4a79-b723-9c4b993af521/invitations?limit=10"
        //    "@type" => "hydra:PartialCollectionView"
        //  ]
        //]

        if (isset($content['hydra:member'])) {
            foreach ($content['hydra:member'] as $invitation) {
                $actor->addInvitation($invitation, $poll);
            }
        } else {
            if (empty($try)) {
                $actor->printTransaction();
                $this->failTrans("response.wrong_format");
            }
        }

    }

    /**
     * @When /^(?P<actor>.+?)(?P<try>| (?:essa[iy]ez?|tente) d['e]) ?accept(?:e[szr]?|ent) l'invitation N°? ?(?P<invitationIndex>.+?) de (?P<otherActor>.+?)$/ui
     * When /^(?P<actor>.+?)(?P<try>| tr(?:y|ies) to) generate (?P<invitationsAmount>.+?) invitations? on the poll titled "(?P<pollSubject>.+)"$/ui
     * @throws Exception
     */
    public function actorAcceptsInvitationOfOtherActor($actor, $try, $invitationIndex, $otherActor)
    {
        $actor = $this->actor($actor);
        $otherActor = $this->actor($otherActor);
        $invitationIndex = $this->number($invitationIndex);

        $invitation = $otherActor->getInvitationByNumber($invitationIndex);
        if (null === $invitation) {
            $this->failTrans("invitation.not_found_by_number");
        }
        $invitationId = $invitation['uuid'];

        $actor->api(
            'GET', "/invitations/{$invitationId}",
            [], [], !empty($try)
        );

        $responseInvitation = $actor->getLastTransaction()->getResponseJson();

        if (!$responseInvitation['acceptedByYou']) {
            $actor->markPreviousRequestAsFailed();
        }
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) d[e']|) ?obt(?:enir|ient)(?: (?:de|à) nouveau)? des informations sur (?:il|elles?|iels?|eux|nous)-mêmes?$/iu
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? (get|obtain)s? information about (?:her|him|them)sel(?:f|ves)$/iu
     */
    public function actorGetsInfoAboutSelf($actor, $try)
    {
        $this->actor($actor)->api(
            'GET', "/me",
            [], [], !empty($try)
        );
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) list(?:e[szr]?|ent)(?: (?:de|à) nouveau)? les scrutins$/iu
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? (?:list|browse)s? the polls$/iu
     */
    public function actorListsPolls($actor, $try)
    {
        $this->actor($actor)->api(
            'GET', "/polls",
            [], [], !empty($try)
        );
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) list(?:e[szr]?|ent)(?: (?:de|à) nouveau)? les scrutins publics$/iu
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? (?:list|browse)s? the public polls$/iu
     */
    public function actorListsPublicPolls($actor, $try)
    {
        $this->actor($actor)->api(
            'GET', "/polls", [],
            [
                'scope' => 'public',
            ], !empty($try)
        );
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) list(?:e[szr]?|ent)(?: (?:de|à) nouveau)? ses(?: propres)? scrutins$/iu
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? (?:list|browse)s? (?:his|her|their) own polls$/iu
     */
    public function actorListsOwnPolls($actor, $try)
    {
        $this->actor($actor)->api(
            'GET', "/polls", [],
            [
                'author' => $this->actor($actor)->getUser()->getUuid(),
            ], !empty($try)
        );
    }


    /**
     * @When /^(?P<actor>.+?)(?P<try> (?:essa[iy]ez?|tente) de|) voir(?: (?:de|à) nouveau)? le scrutin identifié "(?P<pollId>.+)"$/iu
     * @When /^(?P<actor>.+?)(?P<try> tr(?:y|ies) to)? look at the poll (?:with id|identified(?: by| with)?) "(?P<pollId>.+)"$/iu
     */
    public function actorViewsOnePollById($actor, $try, $pollId)
    {
        $this->actor($actor)->api(
            'GET', "/polls/${pollId}",
            [], [], !empty($try)
        );
    }


//    /**
//     * @When /^(?:que )?(?P<actor>.+?) devrait obtenir un SVG validant *:?/ui
//     * @When /^(?P<actor>.+?) should obtain an SVG validating *:?/ui
//     */
//    public function actorShouldObtainSvg($actor, $pystring)
//    {
//        $constraints = $this->yaml($pystring);
//        $response = $this->actor($actor)->getLastTransaction()->getResponse();
//
//        $svg = new Crawler($response->getContent());
//
//        foreach ($constraints as $constraint) {
//            if (isset($constraint['selector'])) {
//
//                if (isset($constraint['amount'])) {
//
//                    $found = $svg->filter($constraint['selector']);
//                    $this->assertEquals(
//                        $constraint['amount'],
//                        $found->count(),
//                        "Incorrect amount of ".$constraint['selector']
//                    );
//
//                }
//            }
//        }
//    }


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

}
