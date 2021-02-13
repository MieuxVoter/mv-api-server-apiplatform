<?php


namespace App\Serializer;


/**
 * See the afferent unit-tests in `tests/`.
 *
 * Class TallyDeserializer
 * @package App\Serializer
 */
class TallyDeserializer
{
    /**
     * Deserializes tallies such as
     *    '0,4,2;1,5,1; 2,3,1 ; 3, 3, 0 ',
     * or
     *    '[0,4,2],[1,5,1],[2,3,1],[3,3,0]',
     * or
     *    '(0,4,2), (1,5,1), (2,3,1), (3,3,0)',
     * or
     *    '0,4,2 / 1,5,1 / 2,3,1 / 3,3,0',
     *
     * @param string $tally_string
     * @return mixed
     */
    public function deserialize(string $tally_string)
    {
        $tally = [];

        $pattern = "/" .
            "\\s*" .
            "(?P<proposals>" .
            "(?:\\s*[0-9][0-9 ]*\\s*[,]?\\s*)+" .
            ")" .
            "\\s*" .
            "/ui";
        $matches = [];
        $has_matched = preg_match_all(
            $pattern,
            $tally_string,
            $matches
        );
        if ($has_matched) {
            foreach ($matches['proposals'] as $proposal_tally_string) {
                $proposal_tally_string = str_replace([" ", "\t"], "", $proposal_tally_string);
                $proposal_matches = [];
                $has_matched_proposal = preg_match_all(
                    "/[0-9]+/ui",
                    $proposal_tally_string,
                    $proposal_matches
                );

                if ($has_matched_proposal) {
                    $tally[] = array_map(function ($p) { return (int) $p; }, $proposal_matches[0]);
                } else {
                    throw new \InvalidArgumentException(
                        "Invalid tally substring.  Recognized example format: 1,4,2,4/4,3,3,1/0,6,4,1"
                    );
                }

            }
        }

        // … add more matchers here if needed

        if ( ! $has_matched) {
            throw new \InvalidArgumentException(
                "Invalid tally string.  Example format: 1,4,2,4 / 4,3,3,1 / 0,6,4,1"
            );
        }

        return $tally;
    }
}
