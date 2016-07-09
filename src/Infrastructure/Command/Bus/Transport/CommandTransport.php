<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

abstract class CommandTransport implements CommandTransportInterface
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
