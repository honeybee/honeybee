<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\CreatePublication;

use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;

class CreatePublicationCommand extends CreateAggregateRootCommand
{
    public function getEventClass()
    {
        return PublicationCreatedEvent::CLASS;
    }
}
