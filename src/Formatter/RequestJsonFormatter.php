<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;

class RequestJsonFormatter extends AbstractRequestFormatter
{
    public function format(RequestInterface $request, array $options = []): string
    {
        $this->options = [];

        $this->extractArguments($request, $options);

        return json_encode($this->options);
    }
}
