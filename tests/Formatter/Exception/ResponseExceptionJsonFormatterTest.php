<?php

namespace Loguzz\Test\Formatter\Exception;

use Exception;
use Loguzz\Formatter\AbstractExceptionFormatter;
use Loguzz\Formatter\ExceptionJsonFormatter;
use Psr\Http\Message\RequestInterface;

class ResponseExceptionJsonFormatterTest extends ResponseExceptionArrayFormatterTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->formatter = new class {
            public function format(RequestInterface $request, Exception $e, array $options = []): array
            {
                return json_decode((new ExceptionJsonFormatter())->format($request, $e, $options), true);
            }
        };
    }

    protected function getFormatter(): AbstractExceptionFormatter
    {
        return new ExceptionJsonFormatter();
    }
}
