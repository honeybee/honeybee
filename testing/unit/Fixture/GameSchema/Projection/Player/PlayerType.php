<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player;

use Honeybee\Tests\Fixture\GameSchema\Projection\ProjectionType;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\GeoPoint\GeoPointAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;

class PlayerType extends ProjectionType
{
    public function __construct()
    {
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
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Player\\Embed\\ProfileType'
                        ]
                    ]
                ),
                new EmbeddedEntityListAttribute(
                    'simple_profiles',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Player\\Embed\\ProfileType'
                        ]
                    ]
                )
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Player::CLASS;
    }
}
