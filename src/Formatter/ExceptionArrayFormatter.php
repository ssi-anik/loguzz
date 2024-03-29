<?php

namespace Loguzz\Formatter;

use Exception;
use Psr\Http\Message\RequestInterface;

class ExceptionArrayFormatter extends AbstractExceptionFormatter
{
    public function format(RequestInterface $request, Exception $e, array $options = []): array
    {
        return $this->parseData($request, $e, $options);
    }
}
