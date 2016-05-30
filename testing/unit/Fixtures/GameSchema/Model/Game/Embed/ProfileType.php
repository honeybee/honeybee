<?php

namespace Honeybee\Tests\Fixtures\GameSchema\Model\Game\Embed;

use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Honeybee\Tests\Fixtures\GameSchema\Model\EntityType;
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
                new Text('alias', $this, [], $parent_attribute),
                new TextListAttribute('tags', $this, [], $parent_attribute),
                new EmbeddedEntityListAttribute(
                    'badges',
                    $this,
                    [
                        'entity_types' => [
                            EntityType::NAMESPACE_PREFIX . 'Game\\Embed\\BadgeType'
                        ]
                    ],
                    $parent_attribute
                ),
                new EntityReferenceListAttribute(
                    'memberships',
                    $this,
                    [
                        'entity_types' => [
                            EntityType::NAMESPACE_PREFIX . 'Game\\Reference\\MembershipType'
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
