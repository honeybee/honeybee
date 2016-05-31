<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor;

use Honeybee\Model\Task\CreateAggregateRoot\AggregateRootCreatedEvent;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;

class AuthorCreatedEvent extends AggregateRootCreatedEvent
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }
}
