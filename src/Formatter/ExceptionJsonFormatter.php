<?php

namespace Loguz\Formatter;

use Exception;

class ExceptionJsonFormatter extends AbstractExceptionFormatter
{
    public function format (Exception $ex, array $options = []) {
        $this->extractArguments($ex, $options);

        return json_encode($this->options);
    }
}