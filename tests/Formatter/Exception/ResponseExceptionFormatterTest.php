<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Loguz\Formatter\ExceptionArrayFormatter;
use PHPUnit\Framework\TestCase;

class ResponseExceptionFormatterTest extends TestCase
{
    /**
     * @var AbstractResponseFormatter
     */
    protected $formatter;
    /** @var Client */
    protected $client;

    public function setUp () : void {
        $this->client = new Client([
            'base_uri'   => 'https://not.a.valid.url.here',
            'user-agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        ]);

        $this->formatter = new ExceptionArrayFormatter();
    }

    public function testException () {
        $request = new Request('GET', '/get');
        try {
            $this->client->send($request);
        } catch ( Exception $e ) {
            $format = $this->formatter->format($e);
        }

        $this->assertArrayHasKey("context", $format);
        $this->assertArrayHasKey("class", $format);
        $this->assertArrayHasKey("code", $format);
        $this->assertArrayHasKey("message", $format);
    }
}