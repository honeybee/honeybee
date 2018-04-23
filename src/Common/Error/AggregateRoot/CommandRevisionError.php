<?php

namespace Honeybee\Common\Error\AggregateRoot;

use Exception;

class CommandRevisionError extends AggregateRootError
{
    private $type;

    private $identifier;

    private $revision;

    public function __construct($message, $type, $identifier, $revision, $code = 0, Exception $previous = null)
    {
        $this->type = $type;
        $this->identifier = $identifier;
        $this->revision = $revision;
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
