<?php

use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\RequestArrayFormatter;
use Loguzz\Test\Formatter\Request\RequestFormatterTest;

class RequestArrayFormatterTest extends RequestFormatterTest
{
    public function implementAssertionForUserAgent($response)
    {
        $this->assertArrayHasKey("user-agent", $response);
        $this->assertSame(self::USER_AGENT, $response['user-agent']);
    }

    public function implementAssertionForRequestMethodIsIncluded($response)
    {
        $this->assertArrayHasKey("method", $response);
    }

    public function implementAssertionForHeadersAreIncluded($response)
    {
        $this->assertArrayHasKey("headers", $response);
        $this->assertArrayHasKey("foo", $response['headers']);
        $this->assertSame(["bar"], $response['headers']['foo']);
    }

    public function implementAssertionForSameHeadersWithMultipleValues($response)
    {
        $this->assertSame(["bar", "baz"], $response['headers']['foo']);
    }

    public function implementAssertionForRequestContainingAllHeaders($response)
    {
        $this->assertArrayHasKey("headers", $response);
        $this->assertSame(["bar"], $response['headers']['foo']);
        $this->assertSame(["baz"], $response['headers']['baz']);
    }

    public function implementAssertionForGetRequestWithQueryString($response)
    {
        $this->assertArrayHasKey('url', $response);
        $this->stringContains('foo=bar', $response['url']);
    }

    public function implementAssertionForGetRequestWithRequestBody($response)
    {
        $this->assertSame('GET', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertSame('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForPostRequest($response)
    {
        $this->assertSame('POST', $response['method']);
        $this->assertNotSame('GET', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertSame('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForHeadRequest($response)
    {
        $this->assertSame('HEAD', $response['method']);
    }

    public function implementAssertionForOptionsRequest($response)
    {
        $this->assertSame('OPTIONS', $response['method']);
    }

    public function implementAssertionForDeleteRequest($response)
    {
        $this->assertSame('DELETE', $response['method']);
    }

    public function implementAssertionForPutRequest($response)
    {
        $this->assertSame('PUT', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertSame('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForPatchRequest($response)
    {
        $this->assertSame('PATCH', $response['method']);
        $this->assertArrayHasKey('body', $response);
        $this->assertSame('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForProperBodyReading($response, $originalContent)
    {
        $this->assertSame($originalContent, $response['body']);
        $this->assertSame('foo=bar&hello=world', $response['body']);
        $this->assertSame("PUT", $response['method']);
    }

    public function implementAssertionForExtractBodyArgument($response)
    {
        $this->assertSame('foo=bar&hello=world', $response['body']);
    }

    public function implementAssertionForCookieIsParsedFromRequest($response)
    {
        $this->assertArrayHasKey('cookies', $response);
        $this->assertCount(1, $response['cookies']);
        $this->assertSame('cookie-name', $response['cookies'][0]['name']);
        $this->assertSame('cookie-value', $response['cookies'][0]['value']);

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
