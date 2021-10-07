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

        $curl = $this->formatter->format($this->getRequest(['headers' => ['foo' => 'bar']]));

        $this->assertEquals(substr_count($curl, "\n"), 2);
    }

    public function testSkipHostInHeaders()
    {
        $curl = $this->formatter->format($this->getRequest());

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGet()
    {
        $curl = $this->formatter->format($this->getRequest());

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGetWithHeader()
    {
        $curl = $this->formatter->format($this->getRequest(['headers' => ['foo' => 'bar']]));

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar'", $curl);
    }

    public function testSimpleGetWithMultipleHeaders()
    {
        $curl = $this->formatter->format($this->getRequest([
            'headers' => [
                'foo' => 'bar',
                'Accept-Encoding' => 'gzip,deflate,sdch',
            ],
        ]));

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar' -H 'Accept-Encoding: gzip,deflate,sdch'", $curl);
    }

    public function testGetWithQueryString()
    {
        $curl = $this->formatter->format($this->getRequest([
            'url' => 'http://example.local?foo=bar',
        ]));

        $this->assertEquals("curl 'http://example.local?foo=bar'", $curl);

        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);

        $curl = $this->formatter->format($this->getRequest([
            'query' => $body,
        ]));

        $this->assertEquals("curl 'http://example.local' -G  -d 'foo=bar&hello=world'", $curl);
    }

    public function testPostRequest()
    {
        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);

        $curl = $this->formatter->format($this->postRequest([
            'body' => $body,
        ]));

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $curl);
        $this->assertStringNotContainsString(" -G ", $curl);
    }

    public function testHeadRequest()
    {
        $request = new Request('HEAD', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("--head", $curl);
    }

    public function testOptionsRequest()
    {
        $request = new Request('OPTIONS', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-X OPTIONS", $curl);
    }

    public function testDeleteRequest()
    {
        $request = new Request('DELETE', 'http://example.local/users/4');
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-X DELETE", $curl);
    }

    public function testPutRequest()
    {
        $request = new Request('PUT', 'http://example.local', [], 'foo=bar&hello=world');
        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $curl);
        $this->assertStringContainsString("-X PUT", $curl);
    }

    public function testUserAgent()
    {
        $curl = $this->formatter->format($this->getRequest([
            'headers' => [
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) Chrome/80.0.3987.149 Safari/537.36',
            ],
        ]));

        $this->assertStringContainsString("-A 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) Chrome/80.0.3987.149 Safari/537.36'",
            $curl);
    }

    public function testProperBodyReading()
    {
        $request = new Request('PUT', 'http://example.local', [], 'foo=bar&hello=world');
        $request->getBody()->getContents();

        $curl = $this->formatter->format($request);

        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $curl);
        $this->assertStringContainsString("-X PUT", $curl);
    }

    public function testExtractBodyArgument()
    {
        $headers = ['X-Foo' => 'Bar'];
        $body = chr(0) . 'foo=bar&hello=world';

        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = new Request('POST', 'http://example.local', $headers, $body);

        $curl = $this->formatter->format($request);

        $this->assertStringContainsString('foo=bar&hello=world', $curl);
    }

    public function testExtractCookieArgument()
    {
        $request = new Request('GET', 'http://example.local', [], 'foo=bar&hello=world');
        $request->getBody()->getContents();

        $curl = $this->formatter->format($request, [
            'cookies' => CookieJar::fromArray(['cookie-name' => 'cookie-value'], 'example.local')
        ]);

        $this->assertStringContainsString("-cookie 'cookie-name=cookie-value'", $curl);
    }
}
