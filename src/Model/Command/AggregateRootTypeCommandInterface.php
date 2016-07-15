<?php

namespace Honeybee\Model\Command;

use Honeybee\Infrastructure\Command\CommandInterface;

interface AggregateRootTypeCommandInterface extends CommandInterface
{
    public function getAggregateRootType();

    public function getEventClass();

    public function getEmbeddedEntityCommands();
}
