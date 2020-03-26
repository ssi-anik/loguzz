<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Loguz\Formatter\RequestCurlFormatter;
use Loguz\Formatter\ResponseJsonFormatter;
use Loguz\Middleware\LogMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

class LogMiddlewareTest extends TestCase
{
    /** @var LoggerInterface */
    protected $logger;

    public function setUp () : void {
        $this->logger = new TestLogger();
    }

    private function getClient ($options = []) {
        $handlerStack = HandlerStack::create();
        $handlerStack->push(new LogMiddleware($this->logger, $options), 'logger');

        if (isset($options['uri'])) {
            $uri = $options['uri'];
        } else {
            $uri = 'https://httpbin.org';
        }

        return new Client([
            'handler'    => $handlerStack,
            'base_uri'   => $uri,
            'user-agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        ]);
    }

    private function getRequest () {
        return new Request('GET', '/get');
    }

    public function testDefaultLogLevel () {
        $options = [
            'log_response' => false,
        ];
        $client = $this->getClient($options);

        $client->send($this->getRequest());
        $this->assertSame(LogLevel::INFO, $this->logger->records[0]['level']);
    }

    public function testDefinedLogLevel () {
        $options = [
            'log_response' => false,
            'log_level'    => 'notice',
        ];
        $client = $this->getClient($options);

        $client->send($this->getRequest());
        $this->assertSame(LogLevel::NOTICE, $this->logger->records[0]['level']);
    }

    public function testNoLog () {
        $client = $this->getClient([
            'log_request'  => false,
            'log_response' => false,
        ]);

        $client->send($this->getRequest());
        $this->assertCount(0, $this->logger->records);
    }

    public function testRequestResponseLogs () {
        $client = $this->getClient();

        $client->send($this->getRequest());
        $this->assertCount(2, $this->logger->records);
    }

    public function testRequestFormatter () {
        $client = $this->getClient([
            'log_response'      => false,
            'request_formatter' => new RequestCurlFormatter,
        ]);

        $client->send($this->getRequest());
        $this->assertCount(1, $this->logger->records);
        $this->assertStringStartsWith('curl', $this->logger->records[0]['message']);
    }

    public function testResponseFormatter () {
        $client = $this->getClient([
            'log_request'        => false,
            'response_formatter' => new ResponseJsonFormatter,
        ]);

        $client->send($this->getRequest());

        $record = $this->logger->records[0]['message'];
        $this->assertStringContainsString('protocol', $record);
        $this->assertStringContainsString('headers', $record);
        $this->assertStringContainsString('body', $record);
    }

    public function testExceptionFormatter () {
        $client = $this->getClient([ 'log_request' => false, 'uri' => 'https://not.a.valid.url.here' ]);

        try {
            $client->send($this->getRequest());
        } catch ( Exception $e ) {
        }

        $record = $this->logger->records[0]['message'];
        $this->assertStringContainsString('class', $record);
        $this->assertStringContainsString('code', $record);
        $this->assertStringContainsString('message', $record);
    }
}