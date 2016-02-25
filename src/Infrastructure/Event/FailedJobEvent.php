<?php

namespace Honeybee\Infrastructure\Event;

use Assert\Assertion;
use Trellis\Common\Object;

class FailedJobEvent extends Event
{
    protected $job_state;

    protected $delivery_info;

    protected $message_headers;

    public function getJobState()
    {
        return $this->job_state;
    }

    public function getDeliveryInfo()
    {
        return $this->delivery_info;
    }

    public function getMessageHeaders()
    {
        return $this->message_headers;
    }

    public function getType()
    {
        return $this->job_state['event']['@type'] . '.failed';
    }

    protected function guardRequiredState()
    {
        Assertion::isArray($this->job_state);
        Assertion::isArray($this->delivery_info);
        Assertion::isArray($this->message_headers);
    }

    public function __toString()
    {
        return sprintf(
            "[Job State]\n%s\n[Delivery Info]\n%s\n[Message Headers]\n%s",
            print_r($this->job_state, true),
            print_r($this->delivery_info, true),
            print_r($this->message_headers, true)
        );
    }
}
