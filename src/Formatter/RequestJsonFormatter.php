<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;

class RequestJsonFormatter extends AbstractRequestFormatter
{
    public function format(RequestInterface $request, array $options = []): string
    {
        return json_encode($this->parseData($request, $options));
    }
}
