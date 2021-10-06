<?php

use GuzzleHttp\Psr7\Request;
use Loguzz\Formatter\RequestJsonFormatter;

class RequestJsonFormatterTest extends RequestArrayFormatterTest
{
    public function setUp(): void
    {
        $this->formatter = new class {
            public function format(Request $request, array $options = []): array
            {
                return json_decode((new RequestJsonFormatter())->format($request, $options), true);
            }
        };
    }
}
