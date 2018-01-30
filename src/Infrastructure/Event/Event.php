<?php

namespace Honeybee\Infrastructure\Event;

use Assert\Assertion;
use DateTimeImmutable;
use Trellis\Common\BaseObject;

abstract class Event extends BaseObject implements EventInterface
{
    const DATE_ISO8601_WITH_MICROS = 'Y-m-d\TH:i:s.uP';

    protected $uuid;

    protected $iso_date;

    protected $metadata;

    public function __construct(array $state = [])
    {
        $this->metadata = [];
        $this->iso_date = DateTimeImmutable::createFromFormat('U.u', sprintf('%.6F', microtime(true)))
            ->format(self::DATE_ISO8601_WITH_MICROS);

        parent::__construct($state);

        $this->guardRequiredState();
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getTimestamp()
    {
        return $this->getDateTime()->format('U');
    }

    public function getDateTime()
    {
        return new DateTimeImmutable($this->iso_date);
    }

    public function getIsoDate()
    {
        return $this->iso_date;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    protected function guardRequiredState()
    {
        Assertion::date($this->iso_date, self::DATE_ISO8601_WITH_MICROS);
        Assertion::uuid($this->uuid);
        Assertion::isArray($this->metadata);
    }

    public function __toString()
    {
        return $this->getType() . '@' . $this->getUuid();
    }
}
