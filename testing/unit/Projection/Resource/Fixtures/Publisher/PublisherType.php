<?php

namespace Honeybee\Tests\Projection\Resource\Fixtures\Publisher;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Projection\Resource\Fixtures\EntityType;

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
                'name' => new Text('name', [ 'mandatory' => true ]),
                'description' => new Text('description')
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publisher::CLASS;
    }
}
