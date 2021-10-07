<?php

namespace Loguzz\Formatter;

use Exception;
use Psr\Http\Message\RequestInterface;

abstract class AbstractExceptionFormatter
{
    protected function parseData(RequestInterface $request, Exception $e, array $options): array
    {
        return [
            'code' => $this->getCode($e),
            'message' => $this->getMessage($e),
            'class' => $this->getExceptionClass($e),
            'context' => $this->getContext($e),
        ];
    }

    final protected function getContext(Exception $e): array
    {
        return method_exists($e, 'getHandlerContext') ? $e->getHandlerContext() : [];
    }

    final protected function getExceptionClass(Exception $e): string
    {
        return get_class($e);
    }

    final protected function getCode(Exception $e): int
    {
        return $e->getCode();
    }

    final protected function getMessage(Exception $e): string
    {
        return $e->getMessage();
    }

    abstract public function format(RequestInterface $request, Exception $e, array $options = []);
}
