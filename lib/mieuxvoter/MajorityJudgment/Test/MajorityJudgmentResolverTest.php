<?php


namespace MieuxVoter\MajorityJudgment\Test;


use MieuxVoter\MajorityJudgment\Resolver\MajorityJudgmentResolver;
use MieuxVoter\MajorityJudgment\Resolver\Options\MajorityJudgmentOptions;
use MieuxVoter\MajorityJudgment\Tally\ArrayPollTally;
use PHPUnit\Framework\TestCase;


class MajorityJudgmentResolverTest extends TestCase
{

    public function testResolve()
    {
        $amountOfJudgments = 21;
        $tallyPerProposal = [
            'proposal_a' => [1, 1, 4, 3, 7, 4, 1],
            'proposal_b' => [0, 2, 4, 3, 7, 4, 1],
        ];
        $expectedResults = [
            [
                'proposal' => 'proposal_b',
                'rank' => 1,
            ],
            [
                'proposal' => 'proposal_a',
                'rank' => 2,
            ],
        ];

        $resolver = new MajorityJudgmentResolver();
        $options = new MajorityJudgmentOptions();
        $pollTally = new ArrayPollTally(
            $amountOfJudgments, $tallyPerProposal
        );
        $result = $resolver->resolve($pollTally, $options);

        $rankedProposals = $result->getRankedProposals();

        $this->assertEquals(
            count($rankedProposals),
            count($expectedResults),
            "The amount of proposals is the same."
        );

        $i = 0;
        foreach ($expectedResults as $expectedResult) {
            $rankedProposal = $rankedProposals[$i];
            $this->assertEquals(
                $expectedResults[$i]['proposal'],
                $rankedProposal->proposal,
                "Proposals are sorted adequately"
            );
            $this->assertEquals(
                $expectedResults[$i]['rank'],
                $rankedProposal->rank,
                "Proposals are ranked adequately"
            );
            $i++;
        }

    }

}
