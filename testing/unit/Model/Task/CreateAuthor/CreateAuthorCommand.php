<?php

namespace Honeybee\Tests\Model\Task\CreateAuthor;

use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;
use Honeybee\Tests\Model\Task\CreateAuthor\AuthorCreatedEvent;

class CreateAuthorCommand extends CreateAggregateRootCommand
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }

    public function getEventClass()
    {
        return AuthorCreatedEvent::CLASS;
    }
}
