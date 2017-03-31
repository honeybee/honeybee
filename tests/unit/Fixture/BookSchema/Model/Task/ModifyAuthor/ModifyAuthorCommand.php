<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Task\ModifyAuthor;

use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;

class ModifyAuthorCommand extends ModifyAggregateRootCommand
{
    public function getEventClass()
    {
        return AuthorModifiedEvent::CLASS;
    }
}
