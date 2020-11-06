<?php


namespace App\Entity\Random;


class Slug
{
    static public function generate() : string
    {
        $lengths = [3, 2, 3];
        $separator = '-';
        $pool = "abcdefghijkmnpqrstuvwxyz";
        $poolLength = strlen($pool);

        $slug = "";
        foreach ($lengths as $i => $length) {
            if (0 < $i) {
                $slug .= $separator;
            }

            for ($n = 0; $n < $length; $n++) {
//                $slug .= substr($pool, random_int(0, $poolLength-1), 1);
                $slug .= (string) $pool{random_int(0, $poolLength-1)};
            }
        }

        return $slug;
    }
}