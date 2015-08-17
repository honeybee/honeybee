<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Trellis\Common\Object;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;

abstract class CommandTransport extends Object implements CommandTransportInterface
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
