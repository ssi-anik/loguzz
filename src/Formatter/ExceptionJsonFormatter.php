<?php

namespace Loguzz\Formatter;

use Exception;

class ExceptionJsonFormatter extends AbstractExceptionFormatter
{
    public function format(Exception $e, array $options = [])
    {
        $this->extractArguments($e, $options);

        return json_encode($this->options);
    }
}
