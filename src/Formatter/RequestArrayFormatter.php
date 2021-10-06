<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;

class RequestArrayFormatter extends AbstractRequestFormatter
{
    /**
     * @param RequestInterface $request
     * @param array $options
     *
     * @return array
     */
    public function format(RequestInterface $request, array $options = [])
    {
        $this->options = [];

        $this->extractArguments($request, $options);

        return $this->options;
    }
}
