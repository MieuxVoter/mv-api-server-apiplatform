<?php

declare(strict_types=1);

namespace App\Controller;


use App\Form\DataTransformer\TallyTransformer;
use Exception;
use MieuxVoter\MajorityJudgment\MajorityJudgmentDeliberator;
use MieuxVoter\MajorityJudgment\Model\Settings\MajorityJudgmentSettings;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



/** @noinspection PhpUnused */
/**
 * Public.
 * Returns JSON.
 *
 * TODO: document endpoint
 * See the related Swagger\Documenter for ApiPlatform documentation and more information.
 * We're waiting on the bump to the latest ApiPlatform (looks more stable now, 2022)
 *
 * How to better integrate this endpoint with ApiPlatform?
 */
final class ResolveTallyController extends AbstractController
{
    /** @noinspection PhpUnused */
    /**
     * @Route(
     *     path="/deliberation",
     *     name="deliberation_via_query",
     * )
     *
     * @param Request $request
     * @param TallyTransformer $tallyTransformer
     * @return Response
     */
    public function tallyFromGet(Request $request, TallyTransformer $tallyTransformer): Response
    {
        $tally_thing = $request->get('tally', ''); // string, array of string, array of array of int
        return $this->respondJsonForTally(
            $tally_thing, $request, $tallyTransformer
        );
    }

    /** @noinspection PhpUnused */
    /**
     * @Route(
     *     path="/{filepath}.json",
     *     name="deliberation_via_path",
     *     requirements={
     *         "filepath"="[0-9-_]+",
     *     }
     * )
     *
     * @param string $filepath
     * @param Request $request
     * @param TallyTransformer $tallyTransformer
     * @return Response
     */
    public function tallyFromFilepath(string $filepath, Request $request, TallyTransformer $tallyTransformer): Response
    {
        $filepath = str_replace('-', ',', $filepath);
        return $this->respondJsonForTally(
            $filepath, $request, $tallyTransformer
        );
    }

    protected function respondJsonForTally($tally_thing, Request $request, TallyTransformer $tallyTransformer): Response
    {
        $tally = null;
        try {
            $tally = $tallyTransformer->reverseTransform($tally_thing);
        } catch (Exception $e) {
            return $this->respondErrorWithDemo($e->getMessage());
        }

        if (empty($tally)) {
            return $this->respondErrorWithDemo("Provided tally is empty.");
        }

        // From JSON in request body ; should we bother merging?  who'd get priority?
//        try {
//            $content = json_decode($request->getContent(), true);
//            $tally = $content['tally'];
//        } catch (\Exception $e) {
//        }

        $participantsAmount = $request->get('participants', 0);
        // Check consistency of participants amounts
        $guessedParticipantsAmount = 0;
        foreach ($tally as $proposalTally) {
            $currentParticipantsAmount = array_sum($proposalTally);
            if ($currentParticipantsAmount > $guessedParticipantsAmount) {
                $guessedParticipantsAmount = $currentParticipantsAmount;
            }
        }
        if ($participantsAmount < 1) {
            $participantsAmount = $guessedParticipantsAmount;
        }
        if ($participantsAmount < $guessedParticipantsAmount) {
            return $this->respondErrorWithDemo("Provided amount of participants is too low.");
        }

        $deliberator = new MajorityJudgmentDeliberator();
        $settings = new MajorityJudgmentSettings();
        $pollTally = new ArrayPollTally(
            $participantsAmount,
            $tally
        );
        $result = $deliberator->deliberate($pollTally, $settings);

        $proposals = [];
        foreach ($result->getProposalResults() as $proposalResult) {

            $proposals[] = [
                'proposal' => $proposalResult->getProposal(),
                'tally' => $proposalResult->getTally(),
                'rank' => $proposalResult->getRank(),
                'median' => $proposalResult->getMedian(),
            ];
        }

        // Since the deliberator sorts the proposals, let's recreate the input order
        usort($proposals, function ($pa, $pb){
            return $pa['proposal'] > $pb['proposal'];
        });

        $response = [  # final specs TBD
            'proposals' => $proposals,
        ];

        return JsonResponse::create($response);
    }

    protected function respondErrorWithDemo($msg="")
    {
        $msg = <<<DOCMSG
$msg

In order to use this endpoint, provide a tally in the following format:

    1-4-2-3-0-4-2__1-2-4-2-1-3-3__2-4-5-0-0-4-1

The structure is as follows:

    <Proposal A Tally>__<Proposal B Tally>__<Proposal C Tally>__…

Each proposal tally is made of the amounts received of each grade, in that order:

    <Lowest Grade>-…-<Passable Grade>-…-<Highest Grade>

DOCMSG;

        return JsonResponse::create([
            'error' => [
                'message' => $msg,
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

}
