<?php

require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Loguz\Formatter\CurlCommandRequestFormatter;
use Loguz\Formatter\CurlJsonRequestFormatter;
use Loguz\Middleware\LogMiddleware;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Namshi\Cuzzle\Middleware\CurlArrayFormatterMiddleware;
use Namshi\Cuzzle\Middleware\CurlFormatterMiddleware;

$logger = new Logger('guzzele.to.curl');
$testHandler = new TestHandler();
$logger->pushHandler($testHandler);

$options = [
    'log_request'   => true,
    //    'log_request' => false,
    'log_formatter' => new CurlCommandRequestFormatter,
    //        'log_formatter' => new CurlJsonRequestFormatter,
    'log_level'     => 'notice',
];

$handlerStack = HandlerStack::create();
$handlerStack->push(new LogMiddleware($logger, $options), 'logger');
//$handlerStack->push(new LogMiddleware($logger, $options), 'logger');
//$handlerStack->before('prepare_body', new LogMiddleware($logger, $options), 'logger');
$client = new Client([ 'handler' => $handlerStack ]); //initialize a Guzzle client

$response = $client->get('http://httpbin.org'); //let's fire a request
/*$response = $client->post('http://httpbin.org/post', [
	'form_params' => [
		'a' => 'a',
		'b' => 'b',
	]
]); //let's fire a request*/

var_dump($testHandler->getRecords()); //check the cURL request in the logs :)