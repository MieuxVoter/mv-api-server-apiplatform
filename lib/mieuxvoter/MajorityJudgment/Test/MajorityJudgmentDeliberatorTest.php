<?php


namespace MieuxVoter\MajorityJudgment\Test;


use MieuxVoter\MajorityJudgment\MajorityJudgmentDeliberator;
use MieuxVoter\MajorityJudgment\Model\Options\MajorityJudgmentOptions;
use MieuxVoter\MajorityJudgment\Model\Tally\ArrayPollTally;
use PHPUnit\Framework\TestCase;


class MajorityJudgmentDeliberatorTest extends TestCase
{

    public function provideDeliberate()
    {
        return [

            [
                # Amount of judgments
                21,
                # Tallies
                [
                    'proposal_a' => [1, 1, 4, 3, 7, 4, 1],
                    'proposal_b' => [0, 2, 4, 3, 7, 4, 1],
                    'proposal_c' => [1, 1, 4, 3, 7, 4, 1],
                    'proposal_d' => [1, 0, 4, 3, 7, 4, 1],
                ],
                # Expectation
                [
                    [
                        'proposal' => 'proposal_b',
                        'rank' => 1,
                    ],
                    [
                        'proposal' => 'proposal_a',
                        'rank' => 2,
                    ],
                    [
                        'proposal' => 'proposal_c',
                        'rank' => 2,
                    ],
                    [
                        'proposal' => 'proposal_d',
                        'rank' => 4,
                    ],
                ],
            ],

            # Dataset: https://github.com/MieuxVoter/mvapi/blob/821a53b2c4b6009c1d8647feb96c754b99b9268b/fixtures/election1.yaml
            [
                # Amount of judgments
                18,
                # Tallies
                [
                    [0, 2, 0, 7, 5, 4],
                    [1, 1, 1, 7, 4, 4],
                    [2, 4, 2, 1, 5, 4],
                    [1, 2, 4, 6, 2, 3],
                    [1, 2, 5, 4, 3, 3],
                    [1, 4, 3, 2, 5, 3],
                    [1, 1, 2, 4, 4, 6],
                    [1, 3, 1, 4, 3, 6],
                    [1, 2, 2, 6, 3, 4],
                    [2, 2, 1, 5, 2, 6],
                    [0, 0, 0, 0, 1, 17],
                    [1, 0, 2, 3, 7, 5],
                    [0, 2, 1, 1, 4, 10],
                    [1, 1, 3, 1, 5, 7],
                    [1, 1, 0, 1, 11, 4],
                    [1, 2, 3, 6, 5, 1],
                    [1, 0, 2, 0, 3, 12],
                ],
                # Expectation
                [
                    [
                        'proposal' => 10,
                        'rank' => 1,
                    ],
                    [
                        'proposal' => 16,
                        'rank' => 2,
                    ],
                    [
                        'proposal' => 12,
                        'rank' => 3,
                    ],
                    [
                        'proposal' => 13,
                        'rank' => 4,
                    ],
                    [
                        'proposal' => 14,
                        'rank' => 5,
                    ],
                    [
                        'proposal' => 11,
                        'rank' => 6,
                    ],
                    [
                        'proposal' => 6,
                        'rank' => 7,
                    ],
                    [
                        'proposal' => 7,
                        'rank' => 8,
                    ],
                    [
                        'proposal' => 0,
                        'rank' => 9,
                    ],
                    [
                        'proposal' => 2,
                        'rank' => 10,
                    ],
                    [
                        'proposal' => 9,
                        'rank' => 11,
                    ],
                    [
                        'proposal' => 1,
                        'rank' => 12,
                    ],
                    [
                        'proposal' => 8,
                        'rank' => 13,
                    ],
                    [
                        'proposal' => 15,
                        'rank' => 14,
                    ],
                    [
                        'proposal' => 3,
                        'rank' => 15,
                    ],
                    [
                        'proposal' => 5,
                        'rank' => 16,
                    ],
                    [
                        'proposal' => 4,
                        'rank' => 17,
                    ],
                ],
            ],

        ];
    }


    /**
     * @dataProvider provideDeliberate
     *
     * @param $amountOfJudgments
     * @param $tallyPerProposal
     * @param $expectedResults
     */
    public function testDeliberate($amountOfJudgments, $tallyPerProposal, $expectedResults) {

        $deliberator = new MajorityJudgmentDeliberator();
        $options = new MajorityJudgmentOptions();
        $pollTally = new ArrayPollTally(
            $amountOfJudgments, $tallyPerProposal
        );
        $result = $deliberator->deliberate($pollTally, $options);

        $proposalResults = $result->getProposalResults();

        $this->assertEquals(
            count($proposalResults),
            count($expectedResults),
            "The amount of proposals is the same."
        );

        $i = 0;
        foreach ($expectedResults as $expectedResult) {
            $proposalResult = $proposalResults[$i];
            $this->assertEquals(
                $expectedResult['proposal'],
                $proposalResult->getProposal(),
                "Proposals are sorted adequately"
            );
            $this->assertEquals(
                $expectedResult['rank'],
                $proposalResult->getRank(),
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
                MajorityJudgmentDeliberator::getMedianGradeIndex($expectation['tallies']),
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
            [$size, $sign, $grade] = MajorityJudgmentDeliberator::getBiggestGroup(
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
