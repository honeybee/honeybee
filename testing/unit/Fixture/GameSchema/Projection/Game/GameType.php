<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game;

use Honeybee\Tests\Fixture\GameSchema\Projection\Game\Embed\ChallengeType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Game\Reference\PlayerType;
use Honeybee\Tests\Fixture\GameSchema\Projection\ProjectionType;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\ReferenceList\ReferenceListAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class GameType extends ProjectionType
{
    /**
     * @param StateMachineInterface $state_machine
     */
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Game',
            [
                new TextAttribute('title', $this, [ 'mandatory' => true ]),
                new EntityListAttribute(
                    'challenges',
                    $this,
                    [ 'entity_types' => [ ChallengeType::CLASS ] ]
                ),
                new ReferenceListAttribute(
                    'players',
                    $this,
                    [ 'entity_types' => [ PlayerType::CLASS ] ]
                )
            ]
        );
    }


    /**
     * @return string
     */
    public function getEntityImplementor()
    {
        return Game::CLASS;
    }
}
