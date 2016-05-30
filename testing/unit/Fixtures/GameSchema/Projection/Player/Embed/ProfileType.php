<?php

namespace Honeybee\Tests\Fixtures\GameSchema\Projection\Player\Embed;

use Honeybee\EntityType;
use Honeybee\Tests\Fixtures\GameSchema\Projection\ProjectionType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\TextList\TextListAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;

class ProfileType extends EntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Profile',
            [
                new Text('alias', $this, [], $parent_attribute),
                new TextListAttribute('tags', $this, [], $parent_attribute),
                new EntityReferenceListAttribute(
                    'teams',
                    $this,
                    [
                        'entity_types' => [
                            ProjectionType::NAMESPACE_PREFIX . 'Player\\Reference\\TeamType',
                            ProjectionType::NAMESPACE_PREFIX . 'Player\\Reference\\ClanType'
                        ]
                    ],
                    $parent_attribute
                ),
                new EmbeddedEntityListAttribute(
                    'badges',
                    $this,
                    [
                        'entity_types' => [
                            ProjectionType::NAMESPACE_PREFIX . 'Player\\Embed\\BadgeType'
                        ]
                    ],
                    $parent_attribute
                )
            ],
            new Options,
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return Profile::CLASS;
    }
}
