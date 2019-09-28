<?php


namespace App\Features;


use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Request;


/**
 * Used in the feature suite.
 *
 * Class ApiTesterClient
 * @package App\Features
 */
class ApiTesterClient extends Client
{

    /**
     * Both our APIs are JSON. (and only JSON, for now)
     * Setting the format to text here is meant to only apply to Symfony's Exception handling,
     * so as to get readable responses with stacktrace in the CLI during development.
     * Ideally this hack should not exist, and our platform should respect the '_format' in all cases,
     * and not force JSON in the REST API ; GraphQL is another story, and may be exempted.
     * Then, we should support a 'error_format' (in test mode) that we could set here or default in config.
     *
     * @inheritDoc
     */
    protected function filterRequest(Request $request)
    {
        $request = parent::filterRequest($request);
        $request->setRequestFormat('txt');

        return $request;
    }

//    public $version = '1';
//
//    /**
//     * @param string $version
//     */
//    public function setVersion(string $version): void
//    {
//        $this->version = $version;
//    }
//
//
//    protected function prefixRoute($route): string
//    {
//        return sprintf('/api/v%s/%s', $this->version, ltrim(trim($route), '/'));
//    }
//
//
//    public function api($method, $route, $parameters=[])
//    {
//        $request = Request::create(
//            $route,
//            $method,
//            $parameters
//        );
//
//        $response = $this->doRequest($request);
//
//        // return response data as array|object, parsed from JSON
//    }
}