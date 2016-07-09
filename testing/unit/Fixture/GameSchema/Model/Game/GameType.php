<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Game;

use Workflux\StateMachine\StateMachineInterface;
use Honeybee\Tests\Fixture\GameSchema\Model\AggregateRootType;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;

class GameType extends AggregateRootType
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
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Model\\Game\\Embed\\ChallengeType'
                        ]
                    ]
                ),
                new EntityReferenceListAttribute(
                    'players',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Model\\Game\\Reference\\PlayerType'
                        ]
                    ]
                )
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Game::CLASS;
    }
}
