<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publisher;

use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Workflux\StateMachine\StateMachineInterface;

class PublisherType extends AggregateRootType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Publisher',
            [
                new Text('name', $this, [ 'mandatory' => true ]),
                new Text('description', $this)
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publisher::CLASS;
    }
}
