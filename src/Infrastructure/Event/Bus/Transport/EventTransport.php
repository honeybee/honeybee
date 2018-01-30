<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Assert\Assertion;
use Trellis\Common\BaseObject;

abstract class EventTransport extends BaseObject implements EventTransportInterface
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
