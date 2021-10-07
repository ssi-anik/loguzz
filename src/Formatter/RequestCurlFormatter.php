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
    protected $options;

    /**
     * @var int
     */
    protected $commandLineLength;

    public function __construct($commandLineLength = 100)
    {
        $this->commandLineLength = $commandLineLength;
    }

    public function setCommandLineLength($commandLineLength): void
    {
        $this->commandLineLength = $commandLineLength;
    }

    protected function addOption($name, $value = null): void
    {
        if (isset($this->options[$name])) {
            if (!is_array($this->options[$name])) {
                $this->options[$name] = (array) $this->options[$name];
            }

            $this->options[$name][] = $value;
        } else {
            $this->options[$name] = $value;
        }
    }

    protected function addToCommand($part): void
    {
        $this->command .= ' ';

        if ($this->commandLineLength > 0 && $this->currentLineLength + strlen($part) > $this->commandLineLength) {
            $this->currentLineLength = 0;
            $this->command .= "\\\n  ";
        }

        $this->command .= $part;
        $this->currentLineLength += strlen($part) + 2;
    }

    protected function generateCurlCommand(): void
    {
        ksort($this->options);

        foreach ($this->options as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $this->addToCommand("-{$name} {$subValue}");
                }
            } elseif (empty($value)) {
                $this->addToCommand("-{$name}");
            } else {
                $this->addToCommand("-{$name} {$value}");
            }
        }
    }

    private function includeHttpMethod($method, $includeGetMethod = false): void
    {
        $method = strtoupper($method);
        if ('HEAD' === $method) {
            $this->addOption('-head');
        } elseif ('GET' !== $method) {
            $this->addOption('X', $method);
        } elseif ($includeGetMethod) {
            $this->addOption('G');
        }
    }

    private function includeRequestBody($body): void
    {
        if (empty($body)) {
            return;
        }

        $this->addOption('d', escapeshellarg($body));
    }

    private function includeCookies($cookies): void
    {
        if (empty($cookies)) {
            return;
        }

        $this->addOption('-cookie', escapeshellarg(implode('; ', array_map(function ($cookie) {
            return sprintf('%s=%s', $cookie['name'], $cookie['value']);
        }, $cookies))));
    }

    private function includeUserAgent($userAgent): void
    {
        if (empty($userAgent)) {
            return;
        }

        $this->addOption('A', escapeshellarg($userAgent));
    }

    private function includeHeaders($headers): void
    {
        if (empty($headers)) {
            return;
        }

        foreach ($headers as $name => $value) {
            foreach ((array) $value as $subValue) {
                $this->addOption('H', escapeshellarg("{$name}: {$subValue}"));
            }
        }
    }

    private function includeUrl($url): void
    {
        $this->addOption('-url', escapeshellarg((string) $url));
    }

    private function prepareCurlOptions($data): void
    {
        $this->includeHttpMethod($data['method'], (bool) strlen($data['body']));
        $this->includeRequestBody($data['body']);
        $this->includeCookies($data['cookies']);
        $this->includeUserAgent($data['user-agent']);
        $this->includeHeaders($data['headers']);
        $this->includeUrl($data['url']);
    }

    public function format(RequestInterface $request, array $options = []): string
    {
        $this->command = 'curl';
        $this->currentLineLength = strlen($this->command);
        $this->options = [];

        $this->prepareCurlOptions($this->parseData($request, $options));

        $this->generateCurlCommand();

        return $this->command;
    }
}
