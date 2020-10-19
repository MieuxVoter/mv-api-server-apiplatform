<?php


namespace App\Features;


//use App\Entity\Citizen;
//use FOS\UserBundle\Model\User;
use App\Entity\User;
use GuzzleHttp\Exception\TransferException;
use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Client as RestClient;
use function DeepCopy\deep_copy;


/**
 * Feature: Do things and remember them
 *   In order to do things and remember them
 *   As an Actor in the Gherkin scenarios
 *   I need an API client and a transaction log
 *
 * This usually represents "I" in the steps.
 *
 * Class Actor
 * @package App\Features
 */
class Actor
{

    /**
     * A HTTP Client for the REST API.
     * Perhaps this dependency should be asked for in the constructor?
     * Right now it is injected by the Actor factory code in BaseFeatureContext#actor()
     *
     * @var RestClient
     */
    protected $client;


    /**
     * A log of transactions in the order they were accomplished, ie. most recent last.
     *
     * @var Transaction[]
     */
    protected $transactions = [];


    protected $api_prefix = '/api';


    /**
     * @var User
     */
    protected $user;


    /**
     * Since we can't recover the password from User#getPlainPassword when we need to,
     * we store the password "on a post-it on the side of the screen", in this variable.
     *
     * @var string
     */
    protected $password;


    /**
     * @return RestClient
     */
    public function getClient(): RestClient
    {
        return $this->client;
    }


    /**
     * @param RestClient $client
     */
    public function setClient(RestClient $client): void
    {
        $this->client = $client;
    }


    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }


    /**
     * @param Transaction $transaction
     * @return Actor
     */
    public function addTransaction(Transaction $transaction): self
    {
        $this->transactions[] = $transaction;

        return $this;
    }


    /**
     * @return Transaction
     * @throws ActorConfusion
     */
    public function getLastTransaction(): Transaction
    {
        $transactionsCount = count($this->getTransactions());
        if (1 > $transactionsCount) {
            throw new ActorConfusion(
                "Trying to read the latest transaction but none happened.\n".
                "Try to request something first?"
            );
        }

        return $this->transactions[$transactionsCount-1];
    }


    /**
     * @return Transaction[]
     * @throws ActorConfusion
     */
    public function getLastTransactions($count): array
    {
        $transactionsCount = count($this->getTransactions());
        if ($count > $transactionsCount) {
            throw new ActorConfusion(
                "Trying to read the latest $count transactions but only $transactionsCount happened.\n"
            );
        }
//        return $this->getTransactions();
        return array_slice($this->transactions, -1 * $count);
    }


    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }


    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }


    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }


    /**
     * @return string
     */
    public function getApiPrefix(): string
    {
        return $this->api_prefix;
    }


    /**
     * @param string $api_prefix
     */
    public function setApiPrefix(string $api_prefix): void
    {
        $this->api_prefix = $api_prefix;
    }


    /**
     * @param $route
     * @return string
     */
    protected function prefixRoute($route)
    {
        return sprintf('%s/%s', $this->getApiPrefix(), ltrim(trim($route), '/'));
    }


    /**
     * Send a request to our API.
     * Tailored for compatibility with API Platform.
     *
     * @param string $method Where are the constants for this?
     * @param string $route
     * @param null $content
     * @param array $parameters
     * @param bool $only_trying
     * @return Transaction
     */
    public function api(string $method, string $route, $content=null, array $parameters=[], bool $only_trying = false): Transaction
    {
        $server = [];
//        $server['HTTP_ACCEPT'] = "application/*json*;q=0.9,text/html,application/xhtml+xml,application/xml;q=0.8,*/*;q=0.7";
        $server['HTTP_ACCEPT'] = "application/ld+json";
        $server['CONTENT_TYPE'] = "application/ld+json";

        $route = $this->prefixRoute($route);

        $content = json_encode($content, JSON_PRETTY_PRINT);

        $this->request($method, $route, $parameters, [], $server, $content);

        $transaction = $this->getLastTransaction();

        if ( ! $only_trying) {
            $this->assertTransactionSuccess($transaction);
        }

        $this->assertTransactionNotServerFailure($transaction);

        return $transaction;
    }


    /**
     * Actually request something, and log the transaction.
     * This does not care about the status of the response, it will log it just like a successful one.
     * Internal tool.  That's why it's not public.  Not adamant on this, make it public if you want to.
     *
     * @param string $method
     * @param string $uri You may provide only the route portion of the URI, when testing the the local kernel.
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param null $content
     * @param bool $changeHistory
     * @return Transaction
     */
    protected function request($method, string $uri, array $parameters = [],
                               array $files = [], array $server = [],
                               $content = null, $changeHistory = true)
    {
        if ( ! empty($this->user)) {
            $server['PHP_AUTH_USER'] = $this->getUser()->getUsername();
            $server['PHP_AUTH_PW']   = $this->getPassword();
        }

//        $server['HTTP_ACCEPT'] = "application/*json*;q=0.9,text/html,application/xhtml+xml,application/xml;q=0.8,*/*;q=0.7";
//        $server['CONTENT_TYPE'] = "application/json";

//        $parameters['_format'] = 'txt';
//        $parameters['error_format'] = 'txt';  // for readable error responses

        $crawler = $this->getClient()->request(
            $method, $uri, $parameters, $files,
            $server, $content, $changeHistory
        );

        $transaction = new Transaction(
            $this->getClient()->getRequest(),
            $this->getClient()->getResponse()
        );
        $this->addTransaction($transaction);

        return $transaction;
    }


    public function printRequest($request = null)
    {
        if (null == $request) {
            $request = $this->getLastTransaction()->getRequest();
        }

        CliPrinter::printRequest($request);
    }


    public function printResponse($response = null)
    {
        if (null == $response) {
            $response = $this->getLastTransaction()->getResponse();
        }

        CliPrinter::printResponse($response);
    }


    public function printTransaction($transaction = null)
    {
        //dump($transaction);
        if (null == $transaction) {
            $transaction = $this->getLastTransaction();
        }

        $this->printRequest($transaction->getRequest());
        $this->printResponse($transaction->getResponse());
    }


    public function printLastTransactions($count)
    {
        $transactions = $this->getLastTransactions($count);

//        dump($transactions[0]->getRequest()->get('variables'));
//        dump($transactions[1]->getRequest()->get('variables'));

        foreach ($transactions as $transaction) {
            $this->printTransaction($transaction);
            print("\n\n");
        }
    }


    /**
     * Assert that the provided transaction was a success.
     *
     * @param Transaction $transaction
     */
    public function assertTransactionSuccess(Transaction $transaction)
    {
        $failure = $this->getPossibleFailureFromTransaction($transaction);

        if (null != $failure) {
            $this->printTransaction($transaction);
            throw new AssertionFailedError($failure);
        }
    }


    /**
     * Assert that the provided transaction was a failure.
     *
     * @param Transaction $transaction
     */
    public function assertTransactionFailure(Transaction $transaction)
    {
        $failure = $this->getPossibleFailureFromTransaction($transaction);

        if (null == $failure) {
            $this->printTransaction($transaction);
            throw new AssertionFailedError("Expected a failure, but it appears to be a success.");
        }
    }


    /**
     * Assert that the provided transaction was not a server failure.
     *
     * @param Transaction $transaction
     */
    public function assertTransactionNotServerFailure(Transaction $transaction)
    {
        $statusCode = $transaction->getResponse()->getStatusCode();

        if ($statusCode >= 500 && $statusCode < 600) {
            $this->printTransaction($transaction);
            throw new AssertionFailedError(sprintf(
                "Server failed with '%d' HTTP status code.".PHP_EOL, $statusCode
            ));
        }
    }


    protected function getPossibleFailureFromTransaction(Transaction $transaction) : ?string
    {
        $failure = null;
        $response = $transaction->getResponse();

        if ( ! $response->isSuccessful()) {
            $statusCode = $response->getStatusCode();
            $failure = sprintf("Response is unsuccessful, with '%d' HTTP status code.".PHP_EOL, $statusCode);
        } else {
            // Our GraphQL bundle does not respect the HTTP status codes.
            // It sends back a 200 even if the query is plain wrong and a 400 is the correct response.
            // Not sure if relevant to GraphQL or the bundle implementation.
            // If it's the bundle, patch it somehow and then we can remove this.
            // https://github.com/overblog/GraphQLBundle/issues/86  …sigh.
            $failure = $this->getPossibleFailureFromPossibleGqlResponse($response);
        }

        return $failure;
    }


    protected function getPossibleFailureFromPossibleGqlResponse(Response $response)
    {
        $failure = null;

        try {
            $content = $response->getContent();

            //$content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $content = json_decode($content, true, 512);
            if (null === $content) {
                throw new JsonException("Cannot decode response content as JSON.");
            }

            if (isset($content['errors'])) {
                $failure = "The transaction dumped above was a failure, ";
                $failure .= "even if the HTTP status code seems OK. (GraphQL >.<)".PHP_EOL;
                $failure .= "Errors:".PHP_EOL;
                foreach ($content['errors'] as $error) {
                    $failure .= "- ".$error['message'];
                    if (isset($error['debugMessage'])) {
                        $failure .= " : ".$error['debugMessage'];
                    }
                    $failure .= PHP_EOL;
                }
            }

        } catch (JsonException $e) {}

        return $failure;
    }
}


// Oddly enough this is not defined on my PHP setup
class JsonException extends \Exception {}
