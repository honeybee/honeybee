<?php

namespace Honeybee\Tests\Model\Task\CreateAuthor;

use Honeybee\Model\Task\CreateAggregateRoot\AggregateRootCreatedEvent;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;

class AuthorCreatedEvent extends AggregateRootCreatedEvent
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }
}
