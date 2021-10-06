<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseJsonFormatter extends AbstractResponseFormatter
{
    public function format(RequestInterface $request, ResponseInterface $response, array $options = []): string
    {
        $this->extractArguments($request, $response, $options);

        return json_encode($this->options);
    }
}
