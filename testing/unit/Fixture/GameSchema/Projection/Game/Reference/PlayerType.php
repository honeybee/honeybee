<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Player\PlayerType as ReferencedPlayerType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Game\Embed\ProfileType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\GeoPoint\GeoPointAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class PlayerType extends ReferencedEntityType
{
    /**
     * @param AttributeInterface|null $parent_attribute
     */
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Player',
            [
                new TextAttribute('name', $this, [ 'mirrored' => true ], $parent_attribute),
                new TextAttribute('tagline', $this, [], $parent_attribute),
                new GeoPointAttribute(
                    'area',
                    $this,
                    [
                        'mirrored' => true,
                        'attribute_alias' => 'location'
                    ],
                    $parent_attribute
                ),
                new EntityListAttribute(
                    'profiles',
                    $this,
                    [ 'entity_types' => [ ProfileType::CLASS ] ],
                    $parent_attribute
                ),
            ],
            [
                'referenced_type' => ReferencedPlayerType::CLASS,
                'identifying_attribute' => 'identifier'
            ],
            $parent_attribute
        );
    }

    /**
     * @inheritdoc
     */
    public function getEntityImplementor()
    {
        return Player::CLASS;
    }
}
