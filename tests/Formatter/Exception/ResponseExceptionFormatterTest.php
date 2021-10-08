<?php

namespace Loguzz\Test\Formatter\Exception;

use Exception;
use GuzzleHttp\Exception\ConnectException;
use Loguzz\Formatter\ExceptionArrayFormatter;
use Loguzz\Test\FormatterTestCase;

class ResponseExceptionFormatterTest extends FormatterTestCase
{
    /**
     * @var \Loguzz\Formatter\AbstractResponseFormatter
     */
    protected $formatter;

    public function testException()
    {
        $request = $this->createRequest();
        $response = new ConnectException('Error connecting the server.', $request);
        $client = $this->getClient($response);
        try {
            $client->send($request);
        } catch (Exception $e) {
            $format = $this->formatter->format($request, $e);
        }

        $this->assertArrayHasKey("context", $format);
        $this->assertArrayHasKey("class", $format);
        $this->assertArrayHasKey("code", $format);
        $this->assertArrayHasKey("message", $format);
    }

    protected function getFormatter(): ExceptionArrayFormatter
    {
        return new ExceptionArrayFormatter();
    }
}
