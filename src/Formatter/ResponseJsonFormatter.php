<?php

namespace Loguz\Formatter;

use Psr\Http\Message\ResponseInterface;

class ResponseJsonFormatter extends AbstractResponseFormatter
{
    public function format (ResponseInterface $response, array $options = []) {
        $this->extractArguments($response, $options);

        return json_encode($this->options);
    }
}