<?php


namespace App\Features;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Model of a HTTP transaction between an Actor and our API,
 * so we can log it in one step and then use it in another.
 *
 * Class Transaction
 * @package App\Features
 */
class Transaction
{

    /**
     * @var Request
     */
    protected $request;


    /**
     * @var Response
     */
    protected $response;

    protected $marked_as_failed = false;


    /**
     * Transaction constructor.  Pretty boring.
     *
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->setRequest($request);
        $this->setResponse($response);
    }


    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }


    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }


    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }


    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * Get the response as an associative PHP array. (usually)
     *
     * @return mixed
     */
    public function getResponseJson()
    {
        $content = $this->response->getContent();
        $json = json_decode($content, true, 16, JSON_OBJECT_AS_ARRAY);

        return $json;
    }

    /**
     * @return bool
     */
    public function isMarkedAsFailed(): bool
    {
        return $this->marked_as_failed;
    }

    /**
     * @param bool $marked_as_failed
     */
    public function setMarkedAsFailed(bool $marked_as_failed): void
    {
        $this->marked_as_failed = $marked_as_failed;
    }

}