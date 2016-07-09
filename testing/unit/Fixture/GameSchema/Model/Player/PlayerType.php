<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Player;

use Honeybee\Tests\Fixture\GameSchema\Model\AggregateRootType;
use Workflux\StateMachine\StateMachineInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\GeoPoint\GeoPointAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;

class PlayerType extends AggregateRootType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Player',
            [
                new Text('name', $this, [ 'mandatory' => true ]),
                new GeoPointAttribute('location', $this, []),
                new EmbeddedEntityListAttribute(
                    'profiles',
                    $this,
                    [
                        'entity_types' => [
                           '\\Honeybee\\Tests\\Fixture\\GameSchema\\Model\\Player\\Embed\\ProfileType'
                        ]
                    ]
                ),
                new EmbeddedEntityListAttribute(
                    'simple_profiles',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Model\\Player\\Embed\\ProfileType'
                        ]
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
