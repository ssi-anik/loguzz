<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;

class RequestArrayFormatter extends AbstractRequestFormatter
{
    public function format(RequestInterface $request, array $options = []): array
    {
        return $this->parseData($request, $options);
    }
}
