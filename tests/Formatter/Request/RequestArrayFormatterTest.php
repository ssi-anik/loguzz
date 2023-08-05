<?php

use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\RequestArrayFormatter;
use Loguzz\Test\Formatter\Request\RequestFormatterTestCase;

class RequestArrayFormatterTest extends RequestFormatterTestCase
{
    public function implementAssertionForUserAgent($response)
    {
        $this->assertArrayHasKey("user-agent", $response);
        $this->assertEquals(self::USER_AGENT, $response['user-agent']);
    }

    public function implementAssertionForRequestMethodIsIncluded($response)
    {
        $this->assertArrayHasKey("method", $response);
    }

    public function implementAssertionForHeadersAreIncluded($response)
    {
        $this->assertArrayHasKey("headers", $response);
        $this->assertArrayHasKey("foo", $response['headers']);
        $this->assertEquals(["bar"], $response['headers']['foo']);
    }

    public function implementAssertionForSameHeadersWithMultipleValues($response)
    {
        $this->assertEquals(["bar", "baz"], $response['headers']['foo']);
    }

    public function implementAssertionForRequestContainingAllHeaders($response)
    {
        $this->assertArrayHasKey("headers", $response);
        $this->assertEquals(["bar"], $response['headers']['foo']);
        $this->assertEquals(["baz"], $response['headers']['baz']);
    }

    public function implementAssertionForGetRequestWithQueryString($response)
    {
        $this->assertArrayHasKey('url', $response);
        $this->stringContains('foo=bar', $response['url']);
    }

    public function implementAssertionForGetRequestWithRequestBody($response)
    {
        $this->assertEquals('GET', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForPostRequest($response)
    {
        $this->assertEquals('POST', $response['method']);
        $this->assertNotEquals('GET', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForHeadRequest($response)
    {
        $this->assertEquals('HEAD', $response['method']);
    }

    public function implementAssertionForOptionsRequest($response)
    {
        $this->assertEquals('OPTIONS', $response['method']);
    }

    public function implementAssertionForDeleteRequest($response)
    {
        $this->assertEquals('DELETE', $response['method']);
    }

    public function implementAssertionForPutRequest($response)
    {
        $this->assertEquals('PUT', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForPatchRequest($response)
    {
        $this->assertEquals('PATCH', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForProperBodyReading($response, $originalContent)
    {
        $this->assertEquals($originalContent, $response['body']);
        $this->assertEquals('foo=bar&hello=world', $response['body']);
        $this->assertEquals("PUT", $response['method']);
    }

    public function implementAssertionForExtractBodyArgument($response)
    {
        $this->assertEquals('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForCookieIsParsedFromRequest($response)
    {
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

    protected function getFormatter(): AbstractRequestFormatter
    {
        return new RequestArrayFormatter();
    }
}
