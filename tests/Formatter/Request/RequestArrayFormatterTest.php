<?php

use GuzzleHttp\Cookie\CookieJar;
use Loguzz\Formatter\RequestArrayFormatter;
use Loguzz\Test\FormatterTestCase;

class RequestArrayFormatterTest extends FormatterTestCase
{
    /**
     * @var RequestArrayFormatter
     */
    protected $formatter;

    protected function getFormatter(): RequestArrayFormatter
    {
        return new RequestArrayFormatter();
    }

    public function testUserAgent()
    {
        $response = $this->formatter->format($this->createRequest());

        $this->assertArrayHasKey("user-agent", $response);
        $this->assertEquals(self::USER_AGENT, $response['user-agent']);
    }

    public function testRequestMethodIsIncluded()
    {
        $response = $this->formatter->format($this->createRequest());

        $this->assertArrayHasKey("method", $response);
    }

    public function testHeadersAreIncluded()
    {
        $response = $this->formatter->format($this->createRequest('GET', '/', '', ['foo' => 'bar']));

        $this->assertArrayHasKey("headers", $response);
        $this->assertArrayHasKey("foo", $response['headers']);
        $this->assertEquals(["bar"], $response['headers']['foo']);
    }

    public function testSameHeadersWithMultipleValues()
    {
        $response = $this->formatter->format($this->createRequest('GET', '/', '', ['foo' => ['bar', 'baz']]));

        $this->assertEquals(["bar", "baz"], $response['headers']['foo']);
    }

    public function testRequestContainsAllHeaders()
    {
        $headers = ['foo' => 'bar', 'baz' => 'baz',];
        $response = $this->formatter->format($this->createRequest('get', '/', '', $headers));

        $this->assertArrayHasKey("headers", $response);
        $this->assertEquals(["bar"], $response['headers']['foo']);
        $this->assertEquals(["baz"], $response['headers']['baz']);
    }

    public function testGetRequestWithQueryString()
    {
        $response = $this->formatter->format($this->createRequest('get', 'http://example.local?foo=bar'));

        $this->assertArrayHasKey('url', $response);
        $this->stringContains('foo=bar', $response['url']);
    }

    public function testGetRequestWithRequestBody()
    {
        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);
        $response = $this->formatter->format($this->createRequest('get', '/', $body));

        $this->assertEquals('GET', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function testPostRequest()
    {
        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);
        $response = $this->formatter->format($this->createRequest('post', '/', $body));

        $this->assertEquals('POST', $response['method']);
        $this->assertNotEquals('GET', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function testHeadRequest()
    {
        $response = $this->formatter->format($this->createRequest('HEAD'));

        $this->assertEquals('HEAD', $response['method']);
    }

    public function testOptionsRequest()
    {
        $response = $this->formatter->format($this->createRequest('OPTIONS'));

        $this->assertEquals('OPTIONS', $response['method']);
    }

    public function testDeleteRequest()
    {
        $response = $this->formatter->format($this->createRequest('DELETE'));

        $this->assertEquals('DELETE', $response['method']);
    }

    public function testPutRequest()
    {
        $response = $this->formatter->format($this->createRequest('PUT', '/', 'foo=bar&hello=world'));

        $this->assertEquals('PUT', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function testProperBodyReading()
    {
        $request = $this->createRequest('PUT', '/', 'foo=bar&hello=world');
        $content = $request->getBody()->getContents();

        $response = $this->formatter->format($request);

        $this->assertEquals($content, $response['body']);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
        $this->assertEquals("PUT", $response['method']);
    }

    public function testExtractBodyArgument()
    {
        $headers = ['X-Foo' => 'Bar'];
        $body = chr(0) . 'foo=bar&hello=world';
        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = $this->createRequest('post', '/', $body, $headers);
        $response = $this->formatter->format($request);

        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function testCookieIsParsedFromRequest()
    {
        $request = $this->createRequest('GET', '/', 'foo=bar&hello=world');

        $response = $this->formatter->format(
            $request,
            [
                'cookies' => CookieJar::fromArray(['cookie-name' => 'cookie-value'], self::BASE_URI),
            ]
        );

        $this->assertArrayHasKey('cookies', $response);
        $this->assertCount(1, $response['cookies']);
        $this->assertEquals('cookie-name', $response['cookies'][0]['name']);
        $this->assertEquals('cookie-value', $response['cookies'][0]['value']);

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
            $this->assertArrayHasKey($key, $response['cookies'][0]);
        }
    }
}
