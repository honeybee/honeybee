<?php

namespace Honeybee\Tests\Projection\Fixtures\Publisher;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Projection\Fixtures\EntityType;

class PublisherType extends EntityType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        parent::__construct('Publisher', $state_machine);
    }

    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
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
