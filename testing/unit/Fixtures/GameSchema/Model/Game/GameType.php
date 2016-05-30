<?php

namespace Honeybee\Tests\Fixtures\GameSchema\Model\Game;

use Workflux\StateMachine\StateMachineInterface;
use Honeybee\Tests\Fixtures\GameSchema\Model\EntityType;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;

class GameType extends EntityType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        parent::__construct('Game', $state_machine);
    }

    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
            [
                new Text('title', $this, [ 'mandatory' => true ]),
                new EmbeddedEntityListAttribute(
                    'challenges',
                    $this,
                    [
                        'entity_types' => [
                            self::NAMESPACE_PREFIX . 'Game\\Embed\\ChallengeType'
                        ]
                    ]
                ),
                new EntityReferenceListAttribute(
                    'players',
                    $this,
                    [
                        'entity_types' => [
                            self::NAMESPACE_PREFIX . 'Game\\Reference\\PlayerType'
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
