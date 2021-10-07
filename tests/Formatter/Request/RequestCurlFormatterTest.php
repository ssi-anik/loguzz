<?php

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\RequestCurlFormatter;
use PHPUnit\Framework\TestCase;

class RequestCurlFormatterTest extends TestCase
{
    /**
     * @var AbstractRequestFormatter
     */
    protected $formatter;

    public function setUp(): void
    {
        $this->formatter = new RequestCurlFormatter();
    }

    private function getRequest($params = []): Request
    {
        $url = 'http://example.local';
        if (isset($params['url'])) {
            $url = $params['url'];
        }

        $headers = [];
        if (isset($params['headers'])) {
            $headers = $params['headers'];

            unset($params['headers']);
        }

        $queries = '';
        if (isset($params['query'])) {
            $queries = $params['query'];
            unset($params);
        }

        return new Request('GET', $url, $headers, $queries);
    }

    private function postRequest($params = []): Request
    {
        $url = '';
        if (isset($params['url'])) {
            $url = $params['url'];
        }

        $headers = [];
        if (isset($params['headers'])) {
            $headers = $params['headers'];
            unset($params['headers']);
        }

        $body = '';
        if (isset($params['body'])) {
            $body = $params['body'];
            unset($params);
        }

        return new Request('POST', $url, $headers, $body);
    }

    public function testMultiLineDisabled()
    {
        $this->formatter->setCommandLineLength(10);

        $result = $this->formatter->format($this->getRequest(['headers' => ['foo' => 'bar']]));

        $this->assertEquals(substr_count($result, "\n"), 2);
    }

    public function testSkipHostInHeaders()
    {
        $result = $this->formatter->format($this->getRequest());

        $this->assertEquals("curl --url 'http://example.local'", $result);
    }

    public function testSimpleGet()
    {
        $result = $this->formatter->format($this->getRequest());

        $this->assertEquals("curl --url 'http://example.local'", $result);
    }

    public function testSimpleGetWithHeader()
    {
        $result = $this->formatter->format($this->getRequest(['headers' => ['foo' => 'bar']]));

        $this->assertEquals("curl --url 'http://example.local' -H 'foo: bar'", $result);
    }

    public function testMultipleHeadersWithSameName()
    {
        $result = $this->formatter->format($this->getRequest(['headers' => ['foo' => ['bar', 'baz']]]));

        $this->assertEquals("curl --url 'http://example.local' -H 'foo: bar' -H 'foo: baz'", $result);
    }

    public function testSimpleGetWithMultipleHeaders()
    {
        $result = $this->formatter->format($this->getRequest([
            'headers' => [
                'foo' => 'bar',
                'Accept-Encoding' => 'gzip,deflate,sdch',
            ],
        ]));

        $expected = "curl --url 'http://example.local' -H 'foo: bar' -H 'Accept-Encoding: gzip,deflate,sdch'";
        $this->assertEquals($expected, $result);
    }

    public function testGetWithQueryString()
    {
        $result = $this->formatter->format($this->getRequest([
            'url' => 'http://example.local?foo=bar',
        ]));

        $this->assertEquals("curl --url 'http://example.local?foo=bar'", $result);

        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);

        $result = $this->formatter->format($this->getRequest([
            'query' => $body,
        ]));

        $this->assertEquals("curl --url 'http://example.local' -G -d 'foo=bar&hello=world'", $result);
    }

    public function testPostRequest()
    {
        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);

        $result = $this->formatter->format($this->postRequest([
            'body' => $body,
        ]));

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $result);
        $this->assertStringNotContainsString(" -G ", $result);
    }

    public function testHeadRequest()
    {
        $request = new Request('HEAD', 'http://example.local');
        $result = $this->formatter->format($request);

        $this->assertStringContainsString("--head", $result);
    }

    public function testOptionsRequest()
    {
        $request = new Request('OPTIONS', 'http://example.local');
        $result = $this->formatter->format($request);

        $this->assertStringContainsString("-X OPTIONS", $result);
    }

    public function testDeleteRequest()
    {
        $request = new Request('DELETE', 'http://example.local/users/4');
        $result = $this->formatter->format($request);

        $this->assertStringContainsString("-X DELETE", $result);
    }

    public function testPutRequest()
    {
        $request = new Request('PUT', 'http://example.local', [], 'foo=bar&hello=world');
        $result = $this->formatter->format($request);

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $result);
        $this->assertStringContainsString("-X PUT", $result);
    }

    public function testUserAgent()
    {
        $agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) Chrome/80.0.3987.149 Safari/537.36';
        $result = $this->formatter->format($this->getRequest([
            'headers' => [
                'user-agent' => $agent,
            ],
        ]));

        $this->assertStringContainsString("-A '$agent'", $result);
    }

    public function testProperBodyReading()
    {
        $request = new Request('PUT', 'http://example.local', [], 'foo=bar&hello=world');
        $request->getBody()->getContents();

        $result = $this->formatter->format($request);

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $result);
        $this->assertStringContainsString("-X PUT", $result);
    }

    public function testExtractBodyArgument()
    {
        $headers = ['X-Foo' => 'Bar'];
        $body = chr(0) . 'foo=bar&hello=world';

        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = new Request('POST', 'http://example.local', $headers, $body);

        $result = $this->formatter->format($request);

        $this->assertStringContainsString('foo=bar&hello=world', $result);
    }

    public function testExtractCookieArgument()
    {
        $request = new Request('GET', 'http://example.local', [], 'foo=bar&hello=world');
        $request->getBody()->getContents();

        $result = $this->formatter->format($request, [
            'cookies' => CookieJar::fromArray(['cookie-name' => 'cookie-value'], 'example.local'),
        ]);

        $this->assertStringContainsString("-cookie 'cookie-name=cookie-value'", $result);
    }
}
