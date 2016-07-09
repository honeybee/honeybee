<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Game\Embed\ProfileType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Player\PlayerType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\GeoPoint\GeoPointAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class PlayerType extends ReferencedEntityType
{
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
                'referenced_type' => PlayerType::CLASS,
                'identifying_attribute' => 'identifier'
            ],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Player::CLASS;
    }
}
