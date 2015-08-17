<?php

namespace Honeybee\Tests\Model\Task\ModifyAuthor;

use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;
use Honeybee\Tests\Model\Aggregate\Author\AuthorType;

class AuthorModifiedEvent extends AggregateRootModifiedEvent
{
    public function getAggregateRootType()
    {
        return AuthorType::CLASS;
    }
}
