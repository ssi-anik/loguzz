<?php

namespace Loguz\Formatter;

use Exception;
use GuzzleHttp\Exception\RequestException;

abstract class AbstractExceptionFormatter
{
    protected $options = [];

    /**
     * @param Exception $e
     * @param array     $options
     */
    protected function extractArguments (Exception $e, array $options) {
        $this->extractExceptionClass($e);
        $this->extractCode($e);
        $this->extractMessage($e);
        if ($e instanceof RequestException) {
            $this->extractContext($e);
        }
    }

    /**
     * @param Exception $e
     */
    private function extractContext (Exception $e) {
        $this->options['context'] = $e->getHandlerContext();
    }

    /**
     * @param Exception $e
     */
    private function extractExceptionClass (Exception $e) {
        $this->options['class'] = get_class($e);
    }

    /**
     * @param Exception $e
     */
    private function extractCode (Exception $e) {
        $this->options['code'] = $e->getCode();
    }

    /**
     * @param Exception $e
     */
    private function extractMessage (Exception $e) {
        $this->options['message'] = $e->getMessage();
    }

    /**
     * @param Exception $e
     * @param array     $options
     *
     * @return string | array
     */
    abstract public function format (Exception $e, array $options = []);
}