<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Publication;

use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;

class PublicationType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Publication',
            [
                new IntegerAttribute('year', [ 'mandatory' => true ]),
                new TextAttribute('description')
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publication::CLASS;
    }
}
