<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game\Embed;

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
                new Text('nickname', $this, [], $parent_attribute),
                new Text('alias', $this, [ 'mirrored' => true ], $parent_attribute),
                new TextListAttribute('tags', $this, [ 'mirrored' => true  ], $parent_attribute),
                new EmbeddedEntityListAttribute(
                    'badges',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Game\\Embed\\BadgeType'
                        ]
                    ],
                    $parent_attribute
                ),
                new EntityReferenceListAttribute(
                    'memberships',
                    $this,
                    [
                        'attribute_alias' => 'teams',
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Game\\Reference\\MembershipType'
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
