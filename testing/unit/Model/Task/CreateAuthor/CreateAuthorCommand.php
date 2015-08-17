<?php

namespace Honeybee\Tests\Model\Task\CreateAuthor;

use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Tests\Model\Aggregate\Author\AuthorType;

class CreateAuthorCommand extends CreateAggregateRootCommand
{
    public function getEventClass()
    {
        return AuthorCreatedEvent::CLASS;
    }

    public function getAggregateRootType()
    {
        return AuthorType::CLASS;
    }
}
