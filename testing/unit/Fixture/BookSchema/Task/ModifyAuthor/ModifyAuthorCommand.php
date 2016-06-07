<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\ModifyAuthor;

use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;

class ModifyAuthorCommand extends ModifyAggregateRootCommand
{
    public function getEventClass()
    {
        return AuthorModifiedEvent::CLASS;
    }
}
