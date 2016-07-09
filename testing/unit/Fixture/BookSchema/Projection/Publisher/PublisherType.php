<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Publisher;

use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class PublisherType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Publisher',
            [
                new TextAttribute('name', $this, [ 'mandatory' => true ]),
                new TextAttribute('description', $this)
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Publisher::CLASS;
    }
}
