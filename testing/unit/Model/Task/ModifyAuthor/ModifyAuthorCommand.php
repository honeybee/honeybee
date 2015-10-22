<?php

namespace Honeybee\Tests\Model\Task\ModifyAuthor;

use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;

class ModifyAuthorCommand extends ModifyAggregateRootCommand
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }

    public function getEventClass()
    {
        return AuthorModifiedEvent::CLASS;
    }
}
