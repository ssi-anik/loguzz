<?php

namespace Loguzz\Formatter;

use Psr\Http\Message\RequestInterface;

class RequestCurlFormatter extends AbstractRequestFormatter
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var int
     */
    protected $currentLineLength;

    /**
     * @var string[]
     */
    protected $format;

    /**
     * @var int
     */
    protected $commandLineLength;

    public function __construct($commandLineLength = 100)
    {
        $this->commandLineLength = $commandLineLength;
    }

    public function format(RequestInterface $request, array $options = []): string
    {
        $this->command = 'curl';
        $this->currentLineLength = strlen($this->command);
        $this->format = [];

        $this->extractArguments($request, $options);
        $this->serializeOptions();
        $this->addOptionsToCommand();

        return $this->command;
    }

    public function setCommandLineLength($commandLineLength): void
    {
        $this->commandLineLength = $commandLineLength;
    }

    protected function addOption($name, $value = null): void
    {
        if (isset($this->format[$name])) {
            if (!is_array($this->format[$name])) {
                $this->format[$name] = (array) $this->format[$name];
            }

            $this->format[$name][] = $value;
        } else {
            $this->format[$name] = $value;
        }
    }

    protected function addCommandPart($part): void
    {
        $this->command .= ' ';

        if ($this->commandLineLength > 0 && $this->currentLineLength + strlen($part) > $this->commandLineLength) {
            $this->currentLineLength = 0;
            $this->command .= "\\\n  ";
        }

        $this->command .= $part;
        $this->currentLineLength += strlen($part) + 2;
    }

    protected function addOptionsToCommand(): void
    {
        ksort($this->format);

        if ($this->format) {
            foreach ($this->format as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $this->addCommandPart("-{$name} {$subValue}");
                    }
                } else {
                    $this->addCommandPart("-{$name} {$value}");
                }
            }
        }
    }

    private function serializeOptions(): void
    {
        $this->serializeHttpMethodOption();
        $this->serializeBodyOption();
        $this->serializeCookiesOption();
        $this->serializeHeadersOption();
        $this->serializeUrlOption();
    }

    private function serializeHttpMethodOption(): void
    {
        if ('GET' !== $this->options['method']) {
            if ('HEAD' === $this->options['method']) {
                $this->addOption('-head');
            } else {
                $this->addOption('X', $this->options['method']);
            }
        }
    }

    private function serializeBodyOption(): void
    {
        if (isset($this->options['data'])) {
            $this->addOption('d', escapeshellarg($this->options['data']));
            if ('GET' == $this->options['method']) {
                $this->addOption('G');
            }
        }
    }

    private function serializeCookiesOption(): void
    {
        if (isset($this->options['cookies'])) {
            $this->addOption('b', escapeshellarg(implode('; ', $this->options['cookies'])));
        }
    }

    private function serializeHeadersOption(): void
    {
        if (isset($this->options['user-agent'])) {
            $this->addOption('A', escapeshellarg($this->options['user-agent']));
        }

        if (isset($this->options['headers'])) {
            foreach ($this->options['headers'] as $name => $value) {
                $this->addOption('H', escapeshellarg("{$name}: {$value}"));
            }
        }
    }

    private function serializeUrlOption(): void
    {
        $this->addCommandPart(escapeshellarg((string) $this->options['url']));
    }
}
