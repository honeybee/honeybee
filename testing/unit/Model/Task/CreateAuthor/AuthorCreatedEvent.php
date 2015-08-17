<?php

namespace Honeybee\Tests\Model\Task\CreateAuthor;

use Honeybee\Model\Task\CreateAggregateRoot\AggregateRootCreatedEvent;
use Honeybee\Tests\Model\Aggregate\Author\AuthorType;

class AuthorCreatedEvent extends AggregateRootCreatedEvent
{
    public function getAggregateRootType()
    {
        return AuthorType::CLASS;
    }
}
