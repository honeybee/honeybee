<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game;

use Honeybee\Tests\Fixture\GameSchema\Projection\ProjectionType;
use Workflux\StateMachine\StateMachineInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;

class GameType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Game',
            [
                new Text('title', $this, [ 'mandatory' => true ]),
                new EmbeddedEntityListAttribute(
                    'challenges',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Game\\Embed\\ChallengeType'
                        ]
                    ]
                ),
                new EntityReferenceListAttribute(
                    'players',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Game\\Reference\\PlayerType'
                        ]
                    ]
                )
            ]
        );
    }


    public static function getEntityImplementor()
    {
        return Game::CLASS;
    }
}
