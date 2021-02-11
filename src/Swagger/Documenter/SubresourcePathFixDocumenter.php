<?php


namespace App\Swagger\Documenter;


use App\Swagger\DocumenterInterface;


/** @noinspection PhpUnused */


class SubresourcePathFixDocumenter implements DocumenterInterface {

    /**
     * Adds custom data to the $docs and returns them.
     *
     * The $context helps knowing whether we're in OASv2 or OASv3.
     *
     * $format is "json"
     * $context is [ "spec_version" => 2, "api_gateway" => false ]
     *
     * @param $docs
     * @param $object
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function document($docs, $object, string $format = null, array $context = []): array
    {
        $old_path = '/polls/{id}/proposals/{proposals}/ballots';
        $new_path = '/polls/{pollId}/proposals/{proposalId}/ballots';

        $docs['paths'][$new_path]['get'] = $docs['paths'][$old_path]['get'];

        unset($docs['paths'][$old_path]);

        foreach ($docs['paths'][$new_path]['get']['parameters'] as $k => $parameter) {
            if ('id' == $parameter['name']) {
                $docs['paths'][$new_path]['get']['parameters'][$k]['name'] = 'pollId';
                $docs['paths'][$new_path]['get']['parameters'][$k]['description'] = "Universally Unique IDentifier of the poll.";
                $docs['paths'][$new_path]['get']['parameters'][$k]['example'] = "6c1c8973-2df3-4b5a-a17d-a3a921dba448";
                continue;
            }
            if ('proposals' == $parameter['name']) {
                $docs['paths'][$new_path]['get']['parameters'][$k]['name'] = 'proposalId';
                $docs['paths'][$new_path]['get']['parameters'][$k]['description'] = "Universally Unique IDentifier of the proposal.";
                $docs['paths'][$new_path]['get']['parameters'][$k]['example'] = "ebf2fda8-5f45-4a33-9758-40d7f5a74998";
                continue;
            }
        }
        
        return $docs;
    }

    /**
     * Documenters are applied in increasing order.
     * Negative values are allowed.  The default value should be 0.
     * You may use the ORDER_XXX constants for this, if you wish.
     * When two or more documenters have the same order,
     * they are applied in the lexicographical order of their class name.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return self::ORDER_FIRST;
    }
}