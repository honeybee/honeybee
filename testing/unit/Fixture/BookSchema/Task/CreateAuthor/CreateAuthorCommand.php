<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor;

use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;

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
