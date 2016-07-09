<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Game\Reference;

use Honeybee\Model\Aggregate\ReferencedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\GeoPoint\GeoPointAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;

class PlayerType extends ReferencedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Player',
            [
                new Text('name', $this, [], $parent_attribute),
                new Text('tagline', $this, [], $parent_attribute),
                new GeoPointAttribute('area', $this, [], $parent_attribute),
                new EmbeddedEntityListAttribute(
                    'profiles',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Model\\Game\\Embed\\ProfileType'
                        ]
                    ],
                    $parent_attribute
                ),
            ],
            new Options(
                [
                    'referenced_type' => '\\Honeybee\\Tests\\Fixture\\GameSchema\\Model\\Player\\PlayerType',
                    'identifying_attribute' => 'identifier'
                ]
            ),
            $parent,
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Player::CLASS;
    }
}
