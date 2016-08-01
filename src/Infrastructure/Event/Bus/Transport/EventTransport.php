<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Assert\Assertion;
use Trellis\Common\Object;

abstract class EventTransport extends Object implements EventTransportInterface
{
    protected $name;

    public function __construct($name)
    {
        Assertion::string($name);
        Assertion::notEmpty($name);

        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
