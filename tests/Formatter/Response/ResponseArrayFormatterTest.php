<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
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

    public function setUp(): void
    {
        $this->client = new Client([
            /*'base_uri' => 'https://httpbin.org',*/
            'user-agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            RequestOptions::ALLOW_REDIRECTS => false,
        ]);

        $this->formatter = new ResponseArrayFormatter();
    }

    public function testResponseKeys()
    {
        $request = new Request('GET', 'https://httpbin.org/cookies/set/name/test');
        $response = $this->client->send($request);
        $format = $this->formatter->format($request, $response);

        $this->assertArrayHasKey("protocol", $format);
        $this->assertArrayHasKey("reason_phrase", $format);
        $this->assertArrayHasKey("status_code", $format);
        $this->assertArrayHasKey("headers", $format);
        $this->assertArrayHasKey("size", $format);
        $this->assertArrayHasKey("body", $format);
        $this->assertArrayHasKey("cookies", $format);
        $this->assertCount(1, $format['cookies']);
        $keys = [
            'name',
            'value',
            'domain',
            'path',
            'max-age',
            'expires',
            'secure',
            'discard',
            'httponly',
        ];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $format['cookies'][0]);
        }
    }
}
