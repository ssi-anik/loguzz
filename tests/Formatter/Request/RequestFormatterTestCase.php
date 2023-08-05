<?php

namespace Loguzz\Test\Formatter\Request;

use GuzzleHttp\Cookie\CookieJar;
use Loguzz\Test\FormatterTestCase;

abstract class RequestFormatterTestCase extends FormatterTestCase
{
    abstract public function implementAssertionForUserAgent($response);

    abstract public function implementAssertionForRequestMethodIsIncluded($response);

    abstract public function implementAssertionForHeadersAreIncluded($response);

    abstract public function implementAssertionForSameHeadersWithMultipleValues($response);

    abstract public function implementAssertionForRequestContainingAllHeaders($response);

    abstract public function implementAssertionForGetRequestWithQueryString($response);

    abstract public function implementAssertionForGetRequestWithRequestBody($response);

    abstract public function implementAssertionForPostRequest($response);

    abstract public function implementAssertionForHeadRequest($response);

    abstract public function implementAssertionForOptionsRequest($response);

    abstract public function implementAssertionForDeleteRequest($response);

    abstract public function implementAssertionForPutRequest($response);

    abstract public function implementAssertionForPatchRequest($response);

    abstract public function implementAssertionForProperBodyReading($response, $originalContent);

    abstract public function implementAssertionForExtractBodyArgument($response);

    abstract public function implementAssertionForCookieIsParsedFromRequest($response);

    public function testUserAgent()
    {
        $response = $this->formatter->format($this->createRequest());

        $this->implementAssertionForUserAgent($response);
    }

    public function testRequestMethodIsIncluded()
    {
        $response = $this->formatter->format($this->createRequest('POST'));

        $this->implementAssertionForRequestMethodIsIncluded($response);
    }

    public function testHeadersAreIncluded()
    {
        $response = $this->formatter->format($this->createRequest('GET', '/', '', ['foo' => 'bar']));

        $this->implementAssertionForHeadersAreIncluded($response);
    }

    public function testSameHeadersWithMultipleValues()
    {
        $response = $this->formatter->format($this->createRequest('GET', '/', '', ['foo' => ['bar', 'baz']]));

        $this->implementAssertionForSameHeadersWithMultipleValues($response);
    }

    public function testRequestContainsAllHeaders()
    {
        $headers = ['foo' => 'bar', 'baz' => 'baz',];
        $response = $this->formatter->format($this->createRequest('get', '/', '', $headers));

        $this->implementAssertionForRequestContainingAllHeaders($response);
    }

    public function testGetRequestWithQueryString()
    {
        $response = $this->formatter->format($this->createRequest('get', 'http://example.local?foo=bar'));

        $this->implementAssertionForGetRequestWithQueryString($response);
    }

    public function testGetRequestWithRequestBody()
    {
        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);
        $response = $this->formatter->format($this->createRequest('get', sprintf('%s/', self::BASE_URI), $body));

        $this->implementAssertionForGetRequestWithRequestBody($response);
    }

    public function testPostRequest()
    {
        $body = http_build_query(['foo' => 'bar', 'hello' => 'world']);
        $response = $this->formatter->format($this->createRequest('post', '/', $body));

        $this->implementAssertionForPostRequest($response);
    }

    public function testHeadRequest()
    {
        $response = $this->formatter->format($this->createRequest('HEAD'));

        $this->implementAssertionForHeadRequest($response);
    }

    public function testOptionsRequest()
    {
        $response = $this->formatter->format($this->createRequest('OPTIONS'));

        $this->implementAssertionForOptionsRequest($response);
    }

    public function testDeleteRequest()
    {
        $response = $this->formatter->format($this->createRequest('DELETE'));

        $this->implementAssertionForDeleteRequest($response);
    }

    public function testPutRequest()
    {
        $response = $this->formatter->format($this->createRequest('PUT', '/', 'foo=bar&hello=world'));

        $this->implementAssertionForPutRequest($response);
    }

    public function testPatchRequest()
    {
        $response = $this->formatter->format($this->createRequest('PATCH', '/', 'foo=bar&hello=world'));

        $this->implementAssertionForPatchRequest($response);
    }

    public function testProperBodyReading()
    {
        $request = $this->createRequest('PUT', '/', 'foo=bar&hello=world');
        $content = $request->getBody()->getContents();

        $response = $this->formatter->format($request);

        $this->implementAssertionForProperBodyReading($response, $content);
    }

    public function testExtractBodyArgument()
    {
        $headers = ['X-Foo' => 'Bar'];
        $body = chr(0) . 'foo=bar&hello=world';
        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = $this->createRequest('post', '/', $body, $headers);
        $response = $this->formatter->format($request);

        $this->implementAssertionForExtractBodyArgument($response);
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

        $this->implementAssertionForCookieIsParsedFromRequest($response);
    }
}
