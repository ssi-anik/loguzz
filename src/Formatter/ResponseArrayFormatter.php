<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseArrayFormatter extends AbstractResponseFormatter
{
    public function format(RequestInterface $request, ResponseInterface $response, array $options = []): array
    {
        return $this->parseData($request, $response, $options);
    }
}
