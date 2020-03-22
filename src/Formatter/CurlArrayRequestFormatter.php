<?php

namespace Loguz\Formatter;

use Psr\Http\Message\RequestInterface;

class CurlArrayRequestFormatter extends AbstractRequestFormatter
{
    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return array
     */
    public function format (RequestInterface $request, array $options = []) {
        $this->options = [];

        $this->extractArguments($request, $options);

        return $this->options;
    }
}