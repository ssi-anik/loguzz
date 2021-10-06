<?php

namespace Loguzz\Formatter;

use Exception;
use Psr\Http\Message\RequestInterface;

abstract class AbstractExceptionFormatter
{
    protected $options = [];

    protected function extractArguments(RequestInterface $request, Exception $e, array $options): void
    {
        $this->extractExceptionClass($e);
        $this->extractCode($e);
        $this->extractMessage($e);
        $this->extractContext($e);
    }

    final protected function extractContext(Exception $e): void
    {
        if (!method_exists($e, 'getHandlerContext')) {
            return;
        }

        $this->options['context'] = $e->getHandlerContext();
    }

    final protected function extractExceptionClass(Exception $e): void
    {
        $this->options['class'] = get_class($e);
    }

    final protected function extractCode(Exception $e): void
    {
        $this->options['code'] = $e->getCode();
    }

    final protected function extractMessage(Exception $e): void
    {
        $this->options['message'] = $e->getMessage();
    }

    abstract public function format(RequestInterface $request, Exception $e, array $options = []);
}
