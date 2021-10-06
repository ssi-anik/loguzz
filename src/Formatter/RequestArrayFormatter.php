<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;

class RequestArrayFormatter extends AbstractRequestFormatter
{
    public function format(RequestInterface $request, array $options = []): array
    {
        $this->options = [];

        $this->extractArguments($request, $options);

        return $this->options;
    }
}
