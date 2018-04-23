<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Task\ModifyAuthor;

use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;

class ConflictingModifyAuthorCommand extends ModifyAggregateRootCommand
{
    public function getEventClass()
    {
        return AuthorModifiedEvent::CLASS;
    }

    public function conflictsWith(AggregateRootEventInterface $event, array &$conflicting_changes = [])
    {
        $conflicting_changes['someconflictingattribute'] = 'someconflictingvalue';
        return true;
    }
}
