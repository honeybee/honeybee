<?php

namespace Honeybee\Infrastructure\Saga;

use Workflux\StatefulSubjectInterface;

class ProjectionNotExistsGuard extends ProjectionExistsGuard
{
    public function accept(StatefulSubjectInterface $subject)
    {
        return !parent::accept($subject);
    }

    public function __toString()
    {
        return static::CLASS;
    }
}
