<?php

declare(strict_types=1);

namespace App\Entity\Random;


use Exception;


class Slug
{
    static public function generate() : string
    {
        // future possible parameters
        $lengths = [3, 2, 3];
        $separator = '-';
        $pool = "abcdefghijkmnpqrstuvwxyz"; // l and o were removed

        $poolLength = strlen($pool);

        $slug = "";
        foreach ($lengths as $i => $length) {
            if (0 < $i) {
                $slug .= $separator;
            }

            for ($n = 0; $n < $length; $n++) {
                $dice_throw = 0;
                try {
                    $dice_throw = random_int(0, $poolLength - 1);
                } catch (Exception $e) {
                    trigger_error("Slug generation failed to use pseudo-random.", E_USER_ERROR);
                }

                $slug .= substr($pool, $dice_throw, 1);
            }
        }

        return $slug;
    }
}