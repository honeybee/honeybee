<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Game;

use Honeybee\Tests\Fixture\GameSchema\Model\AggregateRootType;
use Honeybee\Tests\Fixture\GameSchema\Model\Game\Embed\ChallengeType;
use Honeybee\Tests\Fixture\GameSchema\Model\Game\Reference\PlayerType;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\ReferenceList\ReferenceListAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class GameType extends AggregateRootType
{
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

    public function getEntityImplementor()
    {
        return Game::CLASS;
    }
}
