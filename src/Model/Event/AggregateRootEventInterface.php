<?php

namespace Honeybee\Model\Event;

interface AggregateRootEventInterface
{
    public function getAggregateRootIdentifier();

    public function getAggregateRootType();

    public function getData();

    public function getSeqNumber();
}
