<?php

namespace Honeybee\Tests\Model\Task\ModifyAuthor;

use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;

class AuthorModifiedEvent extends AggregateRootModifiedEvent
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }
}
