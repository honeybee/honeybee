<?php

namespace Honeybee\Model\Command;

use Honeybee\Infrastructure\Command\CommandInterface;

interface EmbeddedEntityTypeCommandInterface extends CommandInterface
{
    public function getEmbeddedEntityType();

    public function getEmbeddedEntityCommands();

    public function getParentAttributeName();
}
