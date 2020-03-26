<?php

namespace Loguz\Formatter;

use Exception;

class ExceptionArrayFormatter extends AbstractExceptionFormatter
{
    public function format (Exception $ex, array $options = []) {
        $this->extractArguments($ex, $options);

        return $this->options;
    }
}