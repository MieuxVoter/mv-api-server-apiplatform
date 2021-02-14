<?php

namespace App\Tests;

use App\Serializer\TallyDeserializer;
use PHPUnit\Framework\TestCase;

class TallyDeserializerTest extends TestCase
{

    function testDemoUsage() {
        $data = [
            [
                'strings' => [
                    '0,4,2;1,5,1; 2,3,1 ; 3, 3, 0 ',
                    '[0,4,2],[1,5,1],[2,3,1],[3,3,0]',
                    '(0,4,2),(1,5,1),(2,3,1),(3,3,0)',
                    '0,4,2 / 1,5,1 / 2,3,1 / 3,3,0',
                ],
                'expected' => [
                    [0, 4, 2],
                    [1, 5, 1],
                    [2, 3, 1],
                    [3, 3, 0],
                ],

            ],
        ];
        foreach ($data as $datum) {

            foreach ($datum['strings'] as $string) {
                $td = new TallyDeserializer();
                $actual = $td->deserialize($string);

                $this->assertEquals($datum['expected'], $actual, "Understands $string");
            }
        }
    }

}
