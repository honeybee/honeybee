<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player;

use Honeybee\Tests\Fixture\GameSchema\Projection\Player\Embed\ProfileType;
use Honeybee\Tests\Fixture\GameSchema\Projection\ProjectionType;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\GeoPoint\GeoPointAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class PlayerType extends ProjectionType
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
                    [ 'entity_types' => [ ProfileType::CLASS ] ]
                ),
                new EntityListAttribute(
                    'simple_profiles',
                    $this,
                    [ 'entity_types' => [ ProfileType::CLASS ] ]
                )
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Player::CLASS;
    }
}
