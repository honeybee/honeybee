<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\ModifyAuthor;

use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;

class AuthorModifiedEvent extends AggregateRootModifiedEvent
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }
}
