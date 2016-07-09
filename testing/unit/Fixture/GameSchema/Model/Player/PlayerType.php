<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Player;

use Honeybee\Tests\Fixture\GameSchema\Model\AggregateRootType;
use Honeybee\Tests\Fixture\GameSchema\Model\Game\Embed\ProfileType;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\GeoPoint\GeoPointAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class PlayerType extends AggregateRootType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Player',
            [
                new TextAttribute('name', $this, [ 'mandatory' => true ]),
                new GeoPointAttribute('location', $this, []),
                new EntityListAttribute(
                    'profiles',
                    $this,
                    [
                        'entity_types' => [ ProfileType::CLASS ]
                    ]
                ),
                new EntityListAttribute(
                    'simple_profiles',
                    $this,
                    [
                        'entity_types' => [ ProfileType::CLASS ]
                    ]
                )
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Player::CLASS;
    }
}
