<?php


namespace App\Ranking;


use App\Entity\Poll;
use App\Entity\Poll\Proposal;
use App\Entity\Poll\Result as PollResult;
use App\Ranking\Options\MajorityJudgmentOptions;
use App\Repository\PollProposalBallotRepository;
use App\Repository\PollRepository;
use MieuxVoter\MajorityJudgment\MajorityJudgmentDeliberator;
use MieuxVoter\MajorityJudgment\Model\Options\MajorityJudgmentOptions as DeliberatorOptions;
use MieuxVoter\MajorityJudgment\Model\Tally\TwoArraysPollTally;


/**
 * Standard Majority Judgment, as described by Balinski & Laraki (2002)
 *
 * Class MajorityJudgmentRanking
 * @package App\Ranking
 */
class MajorityJudgmentRanking implements RankingInterface
{

    /**
     * @var PollRepository
     */
    protected $pollRepository;

    /**
     * @var PollProposalBallotRepository
     */
    protected $ballotRepository;


    /**
     * MajorityJudgmentRanking constructor.
     * @param PollRepository $pollRepository
     * @param PollProposalBallotRepository $ballotRepository
     */
    public function __construct(
        PollRepository $pollRepository,
        PollProposalBallotRepository $ballotRepository
    )
    {
        $this->pollRepository = $pollRepository;
        $this->ballotRepository = $ballotRepository;
    }


    /**
     * The name of the Ranking, as used in the API when choosing the ranking algorithm.
     * Each ranking must have a unique name.
     * If two rankings ever share a name, ??? (we'll see later; we'll probably throw, for safety)
     *
     * @return string
     */
    public function getName(): string
    {
        return "Majority Judgment";
    }

    /**
     * The returned class MUST validate `class_exists()`.
     * Probably best to use the `MayAwesomeOptions::class` syntax in here.
     * This allows each Ranking to have their own custom set of options.
     * If your ranking has no options, return `\App\Ranking\Options\NoOptions::class`.
     *
     * @return string
     */
    public function getOptionsClass(): string
    {
        return MajorityJudgmentOptions::class;
    }

    /**
     * For a given Poll, this computes a Result and returns it
     * This is the heart of the Ranking, where the business logic resides.
     *
     * @param Poll $poll
     * @param mixed $options An instance of the class provided by `getOptionsClass()`.
     * @return PollResult
     */
    public function resolve(Poll $poll, $options): PollResult
    {
        $leaderboard = [];

        $deliberator = new MajorityJudgmentDeliberator();
        $options = new DeliberatorOptions();
        $amountOfParticipants = $this->pollRepository->countParticipants($poll);
        $tallyPerProposal = $this->ballotRepository->getTallyPerProposal($poll);
        $pollTally = new TwoArraysPollTally($amountOfParticipants, $poll->getProposals()->toArray(), array_values($tallyPerProposal));
        $result = $deliberator->deliberate($pollTally, $options);
        $grades = $poll->getGradesInOrder();

//        $proposalsByUuidString = array_map(function (Proposal $p) {
//            return $p->getUuid()->toString();
//        }, $poll->getProposals()->toArray());

        foreach ($result->getRankedProposals() as $rankedProposal) {

            /** @var Proposal $proposal */
            $proposal = $rankedProposal->getProposal();
            $proposalResult = new Poll\Proposal\Result();
            $proposalResult->setProposal($proposal);
            $proposalResult->setRank($rankedProposal->getRank());
            $proposalResult->setTally($amountOfParticipants);
            $proposalResult->setMedianGrade($grades[$rankedProposal->getMedian()]);

            $gradesResults = [];
            $i = 0;
            foreach ($tallyPerProposal[$proposal->getUuid()->toString()] as $gradeTally) {
                $gradeResult = new Poll\Grade\Result();
                $gradeResult->setProposal($proposal);
                $gradeResult->setGrade($grades[$i]);
                $gradeResult->setTally($gradeTally);

                $gradesResults[] = $gradeResult;
                $i++;
            }
            $proposalResult->setGradesResults($gradesResults);

            $leaderboard[] = $proposalResult;
        }

        $pollResult = new PollResult();
        $pollResult->setPoll($poll);
        $pollResult->setAlgorithm($this->getName());
        $pollResult->setLeaderboard($leaderboard);

        return $pollResult;
    }
}