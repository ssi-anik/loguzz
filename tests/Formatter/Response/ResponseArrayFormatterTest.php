<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Loguzz\Formatter\ResponseArrayFormatter;
use PHPUnit\Framework\TestCase;

class ResponseArrayFormatterTest extends TestCase
{
    /**
     * @var AbstractResponseFormatter
     */
    protected $formatter;
    /** @var Client */
    protected $client;

    public function setUp () : void {
        $this->client = new Client([
            'base_uri'   => 'https://httpbin.org',
            'user-agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        ]);
        
        $this->formatter = new ResponseArrayFormatter();
    }

    public function testResponseKey () {
        $request = new Request('GET', '/get');
        $response = $this->client->send($request);
        $format = $this->formatter->format($response);

        $this->assertArrayHasKey("protocol", $format);
        $this->assertArrayHasKey("reason_phrase", $format);
        $this->assertArrayHasKey("status_code", $format);
        $this->assertArrayHasKey("headers", $format);
        $this->assertArrayHasKey("size", $format);
        $this->assertArrayHasKey("body", $format);
    }
}