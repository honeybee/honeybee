<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Trellis\Common\BaseObject;

abstract class CommandTransport extends BaseObject implements CommandTransportInterface
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
