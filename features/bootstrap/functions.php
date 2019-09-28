<?php

/**
 * Returns whatever is in $array1 but not in $array2.
 *
 * @param $array1
 * @param $array2
 * @return array
 */
function array_diff_assoc_recursive($array1, $array2)
{
    $diff = array();
    foreach ($array1 as $k => $v) {
        if (!isset($array2[$k])) {
            $diff[$k] = $v;
        } else if (!is_array($v) && is_array($array2[$k])) {
            $diff[$k] = $v;
        } else if (is_array($v) && !is_array($array2[$k])) {
            $diff[$k] = $v;
        } else if (is_array($v) && is_array($array2[$k])) {
            $array3 = array_diff_assoc_recursive($v, $array2[$k]);
            if (!empty($array3)) $diff[$k] = $array3;
        } else if ((string)$v != (string)$array2[$k]) {
            $diff[$k] = $v;
        }
    }
    return $diff;
}