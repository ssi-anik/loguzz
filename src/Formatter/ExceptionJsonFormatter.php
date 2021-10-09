<?php

namespace Loguzz\Formatter;

use Exception;
use Psr\Http\Message\RequestInterface;

class ExceptionJsonFormatter extends AbstractExceptionFormatter
{
    public function format(RequestInterface $request, Exception $e, array $options = []): string
    {
        return json_encode($this->parseData($request, $e, $options));
    }
}
