<?php

namespace Loguz\Formatter;

use Psr\Http\Message\ResponseInterface;

class ResponseArrayFormatter extends AbstractResponseFormatter
{
    public function format (ResponseInterface $request, array $options = []) {
        $this->extractArguments($response, $options);

        return $this->options;
    }
}