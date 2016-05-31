<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Publisher;

use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;

class PublisherType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Publisher',
            [
                new Text('name', [ 'mandatory' => true ]),
                new Text('description')
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publisher::CLASS;
    }
}
