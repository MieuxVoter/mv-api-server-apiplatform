<?php


namespace App\Swagger\Documenter;


use App\Swagger\Documenter\Ability\TranslatorAbility;
use App\Swagger\DocumenterInterface;


/** @noinspection PhpUnused */


class ResultsDocumenter implements DocumenterInterface
{
    use TranslatorAbility;

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
        //$version = $context['spec_version'];

        $extraDocumentation = [
            'paths' => [
                '/polls/{id}/result' => [
                    'get' => [
                        'tags' => ['Poll'],

                    // This is appended to the previous summary
                    // We need another strategy than array_merge_recursive()
//                        'summary' => $this->trans('result.get_for_poll.summary'),

                        'description' => $this->trans('result.get_for_poll.description'),
                    ],
                ],
            ],
        ];

        // Uuurgh.  That's one way to do it.  …  Let's make our own merger instead?
        $docs['paths']['/polls/{id}/result']['get']['summary'] = $this->trans('result.get_for_poll.summary');

        return array_merge_recursive($docs, $extraDocumentation);
    }
}