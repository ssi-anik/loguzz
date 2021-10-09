<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseJsonFormatter extends AbstractResponseFormatter
{
    public function format(RequestInterface $request, ResponseInterface $response, array $options = []): string
    {
        return json_encode($this->parseData($request, $response, $options));
    }
}
