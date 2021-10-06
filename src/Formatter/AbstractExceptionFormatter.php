<?php

namespace Loguzz\Formatter;

use Exception;

abstract class AbstractExceptionFormatter
{
    protected $options = [];

    /**
     * @param Exception $e
     * @param array $options
     */
    protected function extractArguments(Exception $e, array $options)
    {
        $this->extractExceptionClass($e);
        $this->extractCode($e);
        $this->extractMessage($e);
        $this->extractContext($e);
    }

    /**
     * @param Exception $e
     */
    private function extractContext(Exception $e)
    {
        if (!method_exists($e, 'getHandlerContext')) {
            return;
        }

        $this->options['context'] = $e->getHandlerContext();
    }

    /**
     * @param Exception $e
     */
    private function extractExceptionClass(Exception $e)
    {
        $this->options['class'] = get_class($e);
    }

    /**
     * @param Exception $e
     */
    private function extractCode(Exception $e)
    {
        $this->options['code'] = $e->getCode();
    }

    /**
     * @param Exception $e
     */
    private function extractMessage(Exception $e)
    {
        $this->options['message'] = $e->getMessage();
    }

    /**
     * @param Exception $e
     * @param array $options
     *
     * @return string | array
     */
    abstract public function format(Exception $e, array $options = []);
}
