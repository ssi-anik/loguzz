<?php

use Loguzz\Formatter\AbstractRequestFormatter;
use Loguzz\Formatter\RequestJsonFormatter;
use Psr\Http\Message\RequestInterface;

class RequestJsonFormatterTest extends RequestArrayFormatterTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = new class {
            public function format(RequestInterface $request, array $options = []): array
            {
                return json_decode((new RequestJsonFormatter())->format($request, $options), true);
            }
        };
    }

    public function getFormatter(): AbstractRequestFormatter
    {
        return new RequestJsonFormatter();
    }
}
