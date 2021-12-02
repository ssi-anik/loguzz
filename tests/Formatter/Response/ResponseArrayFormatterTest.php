<?php

namespace Loguzz\Test\Formatter\Response;

use Loguzz\Formatter\AbstractResponseFormatter;
use Loguzz\Formatter\ResponseArrayFormatter;
use Loguzz\Test\FormatterTestCase;

class ResponseArrayFormatterTest extends FormatterTestCase
{
    /**
     * @var AbstractResponseFormatter
     */
    protected $formatter;

    public function getFormatter(): AbstractResponseFormatter
    {
        return new ResponseArrayFormatter();
    }

    public function testAllResponseKeysArePresent()
    {
        $response = $this->createResponse();
        $client = $this->getClient($response);

        $request = $this->createRequest();
        $response = $client->send($request);

        $format = $this->formatter->format($request, $response);

        $this->assertArrayHasKey("protocol", $format);
        $this->assertArrayHasKey("reason_phrase", $format);
        $this->assertArrayHasKey("status_code", $format);
        $this->assertArrayHasKey("headers", $format);
        $this->assertArrayHasKey("size", $format);
        $this->assertArrayHasKey("body", $format);
        $this->assertArrayHasKey("cookies", $format);
    }

    public function testParesesCookiesFromResponse()
    {
        $response = $this->createResponse(
            ['Set-Cookie' => ['cookie-1=cookie-value-1', 'cookie-2=cookie-value-2'],]
        );
        $client = $this->getClient($response);

        $request = $this->createRequest('get', sprintf('%s', self::BASE_URI));
        $response = $client->send($request);

        $format = $this->formatter->format($request, $response);

        $this->assertArrayHasKey("cookies", $format);
        $this->assertCount(2, $format['cookies']);
    }

    public function testAllCookieKeysArePresent()
    {
        $response = $this->createResponse(
            ['Set-Cookie' => ['cookie-1=cookie-value-1'],]
        );
        $client = $this->getClient($response);

        $request = $this->createRequest('get', sprintf('%s', self::BASE_URI));
        $response = $client->send($request);

        $format = $this->formatter->format($request, $response);

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
        $cookie = $format['cookies'][0];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $cookie);
        }
    }

    public function testIncludesAllResponseHeaders()
    {
        $response = $this->createResponse(
            ['x-foo' => 'foo', 'x-baz' => 'baz']
        );
        $client = $this->getClient($response);

        $request = $this->createRequest();
        $response = $client->send($request);

        $format = $this->formatter->format($request, $response);

        $this->assertArrayHasKey("headers", $format);
        $this->assertArrayHasKey('x-foo', $format['headers']);
        $this->assertArrayHasKey('x-baz', $format['headers']);
    }

    public function testHeadersAreArrayType()
    {
        $response = $this->createResponse(
            ['x-foo' => 'foo']
        );
        $client = $this->getClient($response);

        $request = $this->createRequest();
        $response = $client->send($request);

        $format = $this->formatter->format($request, $response);

        $this->assertSame(['foo'], $format['headers']['x-foo']);
    }

    public function testExcludesFewHeaders()
    {
        $response = $this->createResponse(
            [
                'x-foo' => 'foo',
                'Set-Cookie' => ['cookie-1=cookie-value-1', 'cookie-2=cookie-value-2'],
            ]
        );

        $client = $this->getClient($response);

        $request = $this->createRequest('get', sprintf('%s', self::BASE_URI));
        $response = $client->send($request);

        $format = $this->formatter->format($request, $response);

        $excluded = ['set-cookie'];
        foreach ($excluded as $item) {
            $this->assertArrayNotHasKey($item, $format['headers']);
        }

        $this->assertCount(1, $format['headers']);
    }
}
