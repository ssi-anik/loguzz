<?php

use GuzzleHttp\Psr7\Request;
use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\RequestArrayFormatter;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class RequestArrayFormatterTest extends TestCase
{
    static $USER_AGENT = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36";

    /**
     * @var AbstractRequestFormatter
     */
    protected $formatter;

    public function setUp () : void {
        $this->formatter = new RequestArrayFormatter();
    }

    private function getRequest ($params = []) {
        $url = 'http://example.local';
        if (isset($params['url'])) {
            $url = $params['url'];
        }

        $headers = [
            'user-agent' => static::$USER_AGENT,
        ];
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

    private function postRequest ($params = []) {
        $url = '';
        if (isset($params['url'])) {
            $url = $params['url'];
        }

        $headers = [
            'user-agent' => static::$USER_AGENT,
        ];
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

    public function testUserAgent () {
        $curl = $this->formatter->format($this->getRequest());

        $this->assertArrayHasKey("user-agent", $curl);
        $this->assertEquals(static::$USER_AGENT, $curl['user-agent']);
    }

    public function testSimpleGet () {
        $curl = $this->formatter->format($this->getRequest());

        $this->assertArrayHasKey("method", $curl);
    }

    public function testSimpleGetWithHeader () {
        $curl = $this->formatter->format($this->getRequest([
            'headers' => [ 'foo' => 'bar' ],
        ]));

        $this->assertArrayHasKey("headers", $curl);
        $this->assertArrayHasKey("foo", $curl['headers']);
        $this->assertEquals("bar", $curl['headers']['foo']);
    }

    public function testSimpleGetWithMultipleHeaders () {
        $curl = $this->formatter->format($this->getRequest([
            'headers' => [
                'foo'             => 'bar',
                'Accept-Encoding' => 'gzip,deflate,sdch',
            ],
        ]));

        $this->assertArrayHasKey("headers", $curl);
        $this->assertEquals("bar", $curl['headers']['foo']);
        $this->assertEquals("gzip,deflate,sdch", $curl['headers']['Accept-Encoding']);
    }

    public function testGetWithQueryString () {
        $curl = $this->formatter->format($this->getRequest([ 'url' => 'http://example.local?foo=bar' ]));

        $this->assertArrayHasKey('url', $curl);
        $this->stringContains('foo=bar', $curl['url']);

        $body = stream_for(http_build_query([ 'foo' => 'bar', 'hello' => 'world' ], '', '&'));
        $curl = $this->formatter->format($this->getRequest([ 'query' => $body ]));

        $this->assertEquals('GET', $curl['method']);
        $this->assertArrayHasKey('data', $curl);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }

    public function testPostRequest () {
        $body = stream_for(http_build_query([ 'foo' => 'bar', 'hello' => 'world' ], '', '&'));
        $curl = $this->formatter->format($this->postRequest([ 'body' => $body ]));

        $this->assertEquals('POST', $curl['method']);
        $this->assertNotEquals('GET', $curl['method']);
        $this->assertArrayHasKey('data', $curl);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }

    public function testHeadRequest () {
        $request = new Request('HEAD', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertEquals('HEAD', $curl['method']);
    }

    public function testOptionsRequest () {
        $request = new Request('OPTIONS', 'http://example.local');
        $curl = $this->formatter->format($request);

        $this->assertEquals('OPTIONS', $curl['method']);
    }

    public function testDeleteRequest () {
        $request = new Request('DELETE', 'http://example.local/users/4');
        $curl = $this->formatter->format($request);

        $this->assertEquals('DELETE', $curl['method']);
    }

    public function testPutRequest () {
        $request = new Request('PUT', 'http://example.local', [], stream_for('foo=bar&hello=world'));
        $curl = $this->formatter->format($request);

        $this->assertEquals('PUT', $curl['method']);
        $this->assertArrayHasKey('data', $curl);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }

    public function testProperBodyReading () {
        $request = new Request('PUT', 'http://example.local', [], stream_for('foo=bar&hello=world'));
        $content = $request->getBody()->getContents();

        $curl = $this->formatter->format($request);

        $this->assertEquals($content, $curl['data']);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
        $this->assertEquals("PUT", $curl['method']);
    }

    public function testExtractBodyArgument () {
        $headers = [ 'X-Foo' => 'Bar' ];
        $body = chr(0) . 'foo=bar&hello=world';
        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = new Request('POST', 'http://example.local', $headers, stream_for($body));
        $curl = $this->formatter->format($request);

        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }
}