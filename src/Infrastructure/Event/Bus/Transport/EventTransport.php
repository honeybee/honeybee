<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

abstract class EventTransport implements EventTransportInterface
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
