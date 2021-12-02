<?php

use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\RequestCurlFormatter;
use Loguzz\Test\Formatter\Request\RequestFormatterTest;

class RequestCurlFormatterTest extends RequestFormatterTest
{
    /**
     * @var RequestCurlFormatter
     */
    protected $formatter;

    public function getFormatter(): AbstractRequestFormatter
    {
        return new RequestCurlFormatter();
    }

    public function implementAssertionForUserAgent($response)
    {
        $this->assertStringContainsString(sprintf("-A '%s'", self::USER_AGENT), $response);
    }

    public function implementAssertionForRequestMethodIsIncluded($response)
    {
        $this->assertStringContainsString('-X POST', $response);
    }

    public function implementAssertionForHeadersAreIncluded($response)
    {
        $this->assertStringContainsString("-H 'foo: bar'", $response);
    }

    public function implementAssertionForSameHeadersWithMultipleValues($response)
    {
        $this->assertStringContainsString("-H 'foo: bar'", $response);
        $this->assertStringContainsString("-H 'foo: baz'", $response);
    }

    public function implementAssertionForRequestContainingAllHeaders($response)
    {
        $this->assertStringContainsString("-H 'foo: bar'", $response);
        $this->assertStringContainsString("-H 'baz: baz'", $response);
    }

    public function implementAssertionForGetRequestWithQueryString($response)
    {
        $expected = sprintf("curl --url 'http://example.local?foo=bar' -A '%s'", self::USER_AGENT);

        $this->assertSame($expected, $response);
    }

    public function implementAssertionForGetRequestWithRequestBody($response)
    {
        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $response);
        $this->assertStringContainsString('-G', $response);
    }

    public function implementAssertionForPostRequest($response)
    {
        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $response);
        $this->assertStringNotContainsString(" -G ", $response);
    }

    public function implementAssertionForHeadRequest($response)
    {
        $this->assertStringContainsString("--head", $response);
    }

    public function implementAssertionForOptionsRequest($response)
    {
        $this->assertStringContainsString("-X OPTIONS", $response);
    }

    public function implementAssertionForDeleteRequest($response)
    {
        $this->assertStringContainsString("-X DELETE", $response);
    }

    public function implementAssertionForPutRequest($response)
    {
        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $response);
        $this->assertStringContainsString("-X PUT", $response);
    }

    public function implementAssertionForPatchRequest($response)
    {
        $this->assertStringContainsString("-d 'foo=bar&hello=world'", $response);
        $this->assertStringContainsString("-X PATCH", $response);
    }

    public function implementAssertionForProperBodyReading($response, $originalContent)
    {
        $this->assertStringContainsString(sprintf("-d '%s'", $originalContent), $response);
        $this->assertStringContainsString("-X PUT", $response);
    }

    public function implementAssertionForExtractBodyArgument($response)
    {
        $this->assertStringContainsString('foo=bar&hello=world', $response);
    }

    public function implementAssertionForCookieIsParsedFromRequest($response)
    {
        $this->assertStringContainsString("-cookie 'cookie-name=cookie-value'", $response);
    }

    public function testMultiLineDisabled()
    {
        $this->formatter->setCommandLineLength(10);
        $response = $this->formatter->format($this->createRequest('get', '/', '', ['foo' => 'bar']));

        $this->assertSame(3, substr_count($response, "\n"));
    }

    public function testMinimumLineLength()
    {
        $this->formatter->setCommandLineLength(-10);
        // User agent is empty, so it should not be included
        $response = $this->formatter->format(
            $this->createRequest('get', '/', '', ['user-agent' => '', 'foo' => 'bar'])
        );

        $this->assertSame(0, substr_count($response, "\n"));
    }

    public function testDoesNotIncludeEmptyValue()
    {
        $this->formatter->setCommandLineLength(10);
        // User agent is empty, so it should not be included
        $response = $this->formatter->format(
            $this->createRequest('get', '/', '', ['user-agent' => '', 'foo' => 'bar'])
        );

        $this->assertSame(2, substr_count($response, "\n"));
    }

    public function testBasicCurlRequest()
    {
        $response = $this->formatter->format($this->createRequest('get', 'http://example.local'));
        $expected = sprintf("curl --url 'http://example.local' -A '%s'", self::USER_AGENT);

        $this->assertSame($expected, $response);
    }
}
