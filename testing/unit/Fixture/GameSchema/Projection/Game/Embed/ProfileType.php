<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game\Embed;

use Honeybee\Projection\EmbeddedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Model\Game\Embed\BadgeType;
use Honeybee\Tests\Fixture\GameSchema\Model\Game\Reference\MembershipType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\ReferenceList\ReferenceListAttribute;
use Trellis\EntityType\Attribute\TextList\TextListAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class ProfileType extends EmbeddedEntityType
{
    /**
     * @param AttributeInterface|null $parent_attribute
     */
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Profile',
            [
                new TextAttribute('nickname', $this, [], $parent_attribute),
                new TextAttribute('alias', $this, [ 'mirrored' => true ], $parent_attribute),
                new TextListAttribute('tags', $this, [ 'mirrored' => true  ], $parent_attribute),
                new EntityListAttribute(
                    'badges',
                    $this,
                    [ 'entity_types' => [ BadgeType::CLASS ] ],
                    $parent_attribute
                ),
                new ReferenceListAttribute(
                    'memberships',
                    $this,
                    [
                        'attribute_alias' => 'teams',
                        'entity_types' => [ MembershipType::CLASS ]
                    ],
                    $parent_attribute
                )
            ],
            [],
            $parent_attribute
        );
    }

    /**
     * @return string
     */
    public function getEntityImplementor()
    {
        return Profile::CLASS;
    }
}
