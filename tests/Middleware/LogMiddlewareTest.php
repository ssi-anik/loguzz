<?php

use GuzzleHttp\Exception\ConnectException;
use Loguzz\Formatter\ExceptionArrayFormatter;
use Loguzz\Formatter\RequestJsonFormatter;
use Loguzz\Formatter\ResponseArrayFormatter;
use Loguzz\Test\MiddlewareTestCase;
use Psr\Log\LogLevel;

class LogMiddlewareTest extends MiddlewareTestCase
{
    public function testDefaultLogLevelIsDebug()
    {
        $dto = $this->objectFactory();

        $logger = $dto->logger;
        $client = $dto->client;

        $client->send($dto->request);

        foreach ($logger->records as $record) {
            $this->assertEquals(LogLevel::DEBUG, $record['level']);
        }
    }

    public function testUserCanChangeLogLevel()
    {
        $dto = $this->objectFactory(['log_level' => 'info',]);

        $dto->client->send($dto->request);
        $this->assertEquals(LogLevel::INFO, $dto->logger->records[0]['level']);
    }

    public function testUserCanDisableAllTypesOfLogging()
    {
        $dto = $this->objectFactory(
            [
                'log_request' => false,
                'log_response' => false,
            ]
        );

        $dto->client->send($dto->request);
        $this->assertCount(0, $dto->logger->records);
    }

    public function testByDefaultLoggerLogsRequestAndResponse()
    {
        $dto = $this->objectFactory();

        $dto->client->send($dto->request);
        $this->assertCount(2, $dto->logger->records);
    }

    public function testWithoutTagItShouldLogWhateverIsProvidedByTheFormatter()
    {
        $dto = $this->objectFactory();

        $dto->client->send($dto->request);
        $this->assertStringStartsWith('curl', $dto->logger->records[0]['message']);
    }

    public function testUserCanUseCustomTagsForLogging()
    {
        $dto = $this->objectFactory(['tag' => 'custom.tag']);

        $dto->client->send($dto->request);
        $this->assertStringContainsString('{"custom.tag":', $dto->logger->records[0]['message']);
        $this->assertStringContainsString('{"custom.tag":', $dto->logger->records[1]['message']);
    }

    public function testTagsWithRequestAndResponseSeparator()
    {
        $dto = $this->objectFactory(['tag' => 'custom.tag', 'separate' => true]);

        $dto->client->send($dto->request);
        $this->assertStringContainsString('{"custom.tag.request":', $dto->logger->records[0]['message']);
        $this->assertStringContainsString('{"custom.tag.success":', $dto->logger->records[1]['message']);
    }

    public function testTagsSeparatorForFailure()
    {
        $response = new ConnectException('Cannot connect to the host', $this->createRequest());
        $dto = $this->objectFactory(['tag' => 'custom.tag', 'separate' => true], $response);

        try {
            $dto->client->send($dto->request);
        } catch (Exception $e) {
        }

        $this->assertStringContainsString('{"custom.tag.request":', $dto->logger->records[0]['message']);
        $this->assertStringContainsString('{"custom.tag.failure":', $dto->logger->records[1]['message']);
    }

    public function testCustomExceptionFormatter()
    {
        $response = new ConnectException('Cannot connect to the host', $this->createRequest());
        $dto = $this->objectFactory(
            ['exception_formatter' => new ExceptionArrayFormatter(), 'tag' => 'custom.tag', 'separate' => true],
            $response
        );

        try {
            $dto->client->send($dto->request);
        } catch (Exception $e) {
        }

        // double quotes are not escaped meaning the inside data is not JSON.
        $this->assertStringNotContainsString('\"', $dto->logger->records[1]['message']);
    }

    public function testWhenTaggingItShouldLogAsJsonByDefault()
    {
        $dto = $this->objectFactory(['tag' => 'custom.tag']);

        $dto->client->send($dto->request);

        $this->assertIsString($dto->logger->records[0]['message']);
        $this->assertStringContainsString('{"custom.tag"', $dto->logger->records[0]['message']);
        $this->assertStringContainsString('{"custom.tag"', $dto->logger->records[1]['message']);
    }

    public function testWhenTaggingItShouldLogAsArrayIfNotJson()
    {
        $this->markTestSkipped('Based on user preference and LoggerInterface Implementation.');
        $dto = $this->objectFactory(['tag' => 'custom.tag', 'force_json' => false]);

        $dto->client->send($dto->request);
        $this->assertIsArray($dto->logger->records[0]['message']);
        $this->assertArrayHasKey('custom.tag', $dto->logger->records[0]['message']);
        $this->assertArrayHasKey('custom.tag', $dto->logger->records[1]['message']);
    }

    public function testUserCanApplyRequestFormatter()
    {
        $dto = $this->objectFactory(['request_formatter' => new RequestJsonFormatter(),]);

        $dto->client->send($dto->request);
        $this->assertIsString($dto->logger->records[0]['message']);
        $this->assertStringNotContainsString('curl', $dto->logger->records[0]['message']);
    }

    public function testResponseFormatter()
    {
        $this->markTestSkipped('Based on user preference and LoggerInterface Implementation.');
        $dto = $this->objectFactory(['response_formatter' => new ResponseArrayFormatter(),]);

        $dto->client->send($dto->request);
        $loggedMessage = $dto->logger->records[1]['message'];
        $this->assertIsArray($loggedMessage);

        $this->assertArrayHasKey('protocol', $loggedMessage);
        $this->assertArrayHasKey('headers', $loggedMessage);
        $this->assertArrayHasKey('body', $loggedMessage);
    }

    public function testExceptionFormatter()
    {
        $response = new ConnectException('Cannot connect to the host', $this->createRequest());
        $dto = $this->objectFactory([], $response);

        try {
            $dto->client->send($dto->request);
        } catch (Exception $e) {
        }

        $loggedMessage = $dto->logger->records[1]['message'];
        $this->assertStringContainsString('class', $loggedMessage);
        $this->assertStringContainsString('code', $loggedMessage);
        $this->assertStringContainsString('message', $loggedMessage);
    }
}
