<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Publication;

use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;
use Trellis\EntityType\Attribute\Integer\IntegerAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class PublicationType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Publication',
            [
                new IntegerAttribute('year', $this, [ 'mandatory' => true ]),
                new TextAttribute('description', $this)
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Publication::CLASS;
    }
}
