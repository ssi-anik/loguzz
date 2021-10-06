<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\ResponseInterface;

class ResponseArrayFormatter extends AbstractResponseFormatter
{
    public function format(ResponseInterface $response, array $options = []): array
    {
        $this->extractArguments($response, $options);

        return $this->options;
    }
}
