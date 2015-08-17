<?php

namespace Honeybee\Tests\Model\Task\ModifyAuthor;

use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;
use Honeybee\Tests\Model\Aggregate\Author\AuthorType;

class ModifyAuthorCommand extends ModifyAggregateRootCommand
{
    public function getEventClass()
    {
        return AuthorModifiedEvent::CLASS;
    }

    public function getAggregateRootType()
    {
        return AuthorType::CLASS;
    }
}
