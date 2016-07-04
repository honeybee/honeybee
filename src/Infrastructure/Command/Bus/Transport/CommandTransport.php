<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Trellis\Common\Object;

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
