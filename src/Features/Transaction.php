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

}