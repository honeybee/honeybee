<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publication;

use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Trellis\EntityType\Attribute\Integer\IntegerAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class PublicationType extends AggregateRootType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Publication',
            [
                new IntegerAttribute('year', $this, []),
                new TextAttribute('description', $this, [])
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Publication::CLASS;
    }
}
