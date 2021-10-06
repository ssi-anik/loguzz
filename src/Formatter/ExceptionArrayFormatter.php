<?php

namespace Loguzz\Formatter;

use Exception;

class ExceptionArrayFormatter extends AbstractExceptionFormatter
{
    public function format(Exception $e, array $options = [])
    {
        $this->extractArguments($e, $options);

        return $this->options;
    }
}
