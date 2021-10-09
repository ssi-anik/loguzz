<?php

namespace Loguzz\Test;

abstract class FormatterTestCase extends LoguzzTestCase
{
    protected $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = $this->getFormatter();
    }

    abstract protected function getFormatter();
}
