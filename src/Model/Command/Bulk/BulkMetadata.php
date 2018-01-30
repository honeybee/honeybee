<?php

namespace Honeybee\Model\Command\Bulk;

use Trellis\Common\BaseObject;

class BulkMetadata extends BaseObject implements BulkMetadataInterface
{
    protected $type;

    protected $identifier;

    protected $command;

    public function __construct($type, $identifier, $command)
    {
        $this->type = $type;
        $this->identifier = $identifier;
        $this->command = $command;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}
