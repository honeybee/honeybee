<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player\Embed;

use Honeybee\Projection\EmbeddedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\TextList\TextListAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;

class ProfileType extends EmbeddedEntityType
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
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Player\\Reference\\TeamType',
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Player\\Reference\\ClanType'
                        ]
                    ],
                    $parent_attribute
                ),
                new EmbeddedEntityListAttribute(
                    'badges',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Player\\Embed\\BadgeType'
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

    public function getEntityImplementor()
    {
        return Profile::CLASS;
    }
}
