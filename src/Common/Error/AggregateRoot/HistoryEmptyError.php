<?php

namespace Honeybee\Common\Error\AggregateRoot;

use Exception;

class HistoryEmptyError extends AggregateRootError
{
    private $type;

    private $identifier;

    private $revision;

    public function __construct($message, $type, $cmd_identifier, $cmd_revision, $code = 0, Exception $previous = null)
    {
        $this->type = $type;
        $this->identifier = $cmd_identifier;
        $this->revision = $cmd_revision;
        parent::__construct($message, $code, $previous);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getRevision()
    {
        return $this->revision;
    }
}
