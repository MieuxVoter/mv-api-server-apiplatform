<?php


namespace App\Features;


use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;


/**
 * Prints things for the CLI.
 * Mostly for use in a feature suite.
 *
 * Next
 * - JSON
 * - Will we at some point want|need to override the color scheme?
 *   If yes, static methods are a no-go.  Make a service?
 *
 * Class CliPrinter
 * @package App\Features
 */
class CliPrinter
{

    /**
     * Nicely print the request for CLI, with colors.
     *
     * @param Request $request
     * @param bool $only_return Set to true to skip the actual print() operation.
     * @return string
     */
    static public function printRequest(Request $request, $only_return = false) : string
    {
        $uriStyle = new OutputFormatterStyle('magenta', null, ['bold']);
        $methodStyle = new OutputFormatterStyle('yellow', null, ['bold']);

        /// Straight from Request.__toString
        try {
            $content = $request->getContent();
        } catch (\LogicException $e) {
            trigger_error($e, E_USER_ERROR);
            $content = "";
        }

        $cookieHeader = '';
        $cookies = [];

        foreach ($request->cookies as $k => $v) {
            $cookies[] = $k.'='.$v;
        }

        if (!empty($cookies)) {
            $cookieHeader = 'Cookie: '.implode('; ', $cookies)."\r\n";
        }
        ////////////////////////////////////

        $dumper = new CliDumper();
        $dumper->setColors(true);
        $cloner = new VarCloner();
        $parameters = $dumper->dump($cloner->cloneVar($request->request->all()), true);

        $d = sprintf(
            "%s %s %s\r\n%s\r\n%s\r\n%s\n",
            $methodStyle->apply($request->getMethod()),
            $uriStyle->apply($request->getRequestUri()),
            $request->getProtocolVersion(),
            $request->headers,
            $parameters,
            $cookieHeader . $content
        );

        if ( ! $only_return) {
            print($d);
        }

        return $d;
    }


    /**
     * Nicely print the response for CLI, with colors.
     *
     * @param Response $response
     * @param bool $only_return
     * @return string
     */
    static public function printResponse(Response $response, $only_return = false) : string
    {
        $traceMax = 16;
        $statusStyle = new OutputFormatterStyle(null, null, ['bold']);
        $statusCode = $response->getStatusCode();

        if ($statusCode < 300) {
            $statusStyle->setForeground('green');
        } else if ($statusCode < 400) {
            $statusStyle->setForeground('blue');
        } else {
            $statusStyle->setForeground('red');
        }

        $d = sprintf(
            "%s HTTP/%s\r\n%s\r\n",
            $statusStyle->apply($statusCode." ".Response::$statusTexts[$statusCode]),
            $response->getProtocolVersion(),
            $response->headers
        );

        $responseBody = $response->getContent();

        if (true) { // if behat's verbosity is, say, higher than -v ?
            try {
                $jsonResponse = json_decode($response->getContent(), true);
                if (null == $jsonResponse) {
                    throw new \Exception();
                }
                if (isset($jsonResponse['trace'])) {
                    $traceTally = count($jsonResponse['trace']);
                    $jsonResponse['trace'] = array_slice($jsonResponse['trace'], 0, $traceMax);
                    if ($traceTally > $traceMax) {
                        $jsonResponse['trace'][] = "… (".($traceTally-$traceMax)." more hidden)";
                    }
                }
                $responseBody = self::dump($jsonResponse);
                $responseBody = "The actual JSON response is hard to read.\r\n" .
                    "Here is a dump of it, once decoded:\r\n" .
                    $responseBody;
            } catch (\Exception $e) {
                // Response is not valid JSON, just print it as-is
            }
        }

        $d .= sprintf("%s\r\n", $responseBody);

        if ( ! $only_return) {
            print($d);
            //dump(json_decode($response->getContent()));
        }

        return $d;
    }


    /**
     * Nicely print the whole transaction for CLI, with colors.
     *
     * @param Transaction $transaction
     * @param bool $only_return
     * @return string
     */
    static public function printTransaction(Transaction $transaction, $only_return = false) : string
    {
        $d = "";
        $d .= self::printRequest($transaction->getRequest(), $only_return);
        $d .= self::printResponse($transaction->getResponse(), $only_return);

        return $d;
    }


    static public function dump($var) : string
    {
        $cloner = new VarCloner();
        $dumper = new CliDumper();

        return $dumper->dump($cloner->cloneVar($var), true);
    }

}