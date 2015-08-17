<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Trellis\Common\Object;

abstract class EventTransport extends Object implements EventTransportInterface
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
