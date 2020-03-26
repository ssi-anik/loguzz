<?php

require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Loguzz\Formatter\RequestCurlFormatter;
use Loguzz\Middleware\LogMiddleware;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Namshi\Cuzzle\Middleware\CurlArrayFormatterMiddleware;
use Namshi\Cuzzle\Middleware\CurlFormatterMiddleware;

$logger = new Logger('guzzele.to.curl');
$testHandler = new TestHandler();
$logger->pushHandler($testHandler);

$options = [
    'length'            => 200,
    // 'log_request'   => true,
    'log_request'       => false,
    'log_response'      => true,
    // 'log_response'  => false,
    'log_level'         => 'notice',
    'request_formatter' => new RequestCurlFormatter,
];

$handlerStack = HandlerStack::create();
$handlerStack->push(new LogMiddleware($logger, $options), 'logger');
//$handlerStack->push(new LogMiddleware($logger, $options), 'logger');
//$handlerStack->before('prepare_body', new LogMiddleware($logger, $options), 'logger');
//$client = new Client([ 'handler' => $handlerStack , 'http_errors' => false]); //initialize a Guzzle client
$client = new Client([ 'handler' => $handlerStack, 'http_errors' => false ]); //initialize a Guzzle client

//$response = $client->get('http://httpbin.org');
//$response = $client->post('http://httpbin.org/put');
//$response = $client->post('https://127.0.0.1:8012');
//$response = $client->post('http://google.com');
try {
    $response = $client->put('https://httpbin.org/post', [ 'form_params' => [ 'a' => 'a', 'b' => 'b', ] ]);
} catch ( Exception $e ) {
}

var_dump($testHandler->getRecords()); //check the cURL request in the logs :)
/*var_dump((new RequestCurlFormatter())->format(new Request('GET', 'http://httpbin.org/get', [
    'agent' => 'curl',
], 'param1=param1&param2=param2')));*/