<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Publisher;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Model\Aggregate\Fixtures\EntityType;
use Workflux\StateMachine\StateMachineInterface;

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
