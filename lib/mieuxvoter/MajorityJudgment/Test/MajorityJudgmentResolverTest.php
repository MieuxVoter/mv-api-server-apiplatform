<?php


namespace MieuxVoter\MajorityJudgment\Test;


use MieuxVoter\MajorityJudgment\MajorityJudgmentResolver;
use MieuxVoter\MajorityJudgment\Model\Options\MajorityJudgmentOptions;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;
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
                $rankedProposal->getProposal(),
                "Proposals are sorted adequately"
            );
            $this->assertEquals(
                $expectedResults[$i]['rank'],
                $rankedProposal->getRank(),
                "Proposals are ranked adequately"
            );
            $i++;
        }

    }


    public function testGetMedianGradeIndex()
    {
        $expectations = [
            [
                'tallies' => [1, 1],
                'index' => 0,
            ],
            [
                'tallies' => [2, 2, 2],
                'index' => 1,
            ],
            [
                'tallies' => [2, 2, 7],
                'index' => 2,
            ],
            [
                'tallies' => [2, 2, 5, 1, 3],
                'index' => 2,
            ],
            [
                'tallies' => [2, 3, 5, 7, 11, 13],
                'index' => 4,
            ],
            [
                'tallies' => [0, 0, 0, 0, 0, 0],
                'index' => 0,
            ],
            [
                'tallies' => [0, 0, 0, 1, 0, 0],
                'index' => 3,
            ],
            [
                'tallies' => [0, 0, 1, 0, 1, 0],
                'index' => 2,
            ],
            [
                'tallies' => [0, 2, 2],
                'index' => 1,
            ],
        ];

        foreach ($expectations as $expectation) {
            $this->assertEquals(
                $expectation['index'],
                MajorityJudgmentResolver::getMedianGradeIndex($expectation['tallies']),
                "Found the expected median grade index."
            );
        }
    }

    function testBiggestGroup()
    {
        $expectations = [
            [
                'tallies' => [1, 4, 7, 0, 6],
                'around' => 2, // median
                'size' => 6,
                'sign' => 1,
                'grade' => 4,
            ],
            [
                'tallies' => [1, 2, 1, 0, 6],
                'around' => 4, // median
                'size' => 4,
                'sign' => -1,
                'grade' => 2,
            ],
            [
                'tallies' => [1, 2, 1, 0, 6],
                'around' => 4, // median
                'size' => 4,
                'sign' => -1,
                'grade' => 2,
            ],
            [
                'tallies' => [0, 1, 0, 1, 0],
                'around' => 1, // median
                'size' => 1,
                'sign' => 1,
                'grade' => 3,
            ],
            [
                'tallies' => [0, 0, 0, 17, 0],
                'around' => 3, // median
                'size' => 0,
                'sign' => -1,
                'grade' => 0,
            ],
            [
                'tallies' => [5, 0, 0, 0, 0],
                'around' => 0, // median
                'size' => 0,
                'sign' => -1,
                'grade' => 0,
            ],
        ];

        foreach ($expectations as $expectation) {
            [$size, $sign, $grade] = MajorityJudgmentResolver::getBiggestGroup(
                $expectation['around'],
                $expectation['tallies']
            );
            $this->assertEquals(
                $expectation['size'], $size,
                "Group size matches."
            );
            $this->assertEquals(
                $expectation['sign'], $sign,
                "Group sign matches."
            );
            $this->assertEquals(
                $expectation['grade'], $grade,
                "Group grade matches."
            );
        }
    }

}
