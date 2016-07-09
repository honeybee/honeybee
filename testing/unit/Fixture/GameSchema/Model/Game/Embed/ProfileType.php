<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Game\Embed;

use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Model\Game\Embed\BadgeType;
use Honeybee\Tests\Fixture\GameSchema\Model\Game\Reference\MembershipType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\ReferenceList\ReferenceListAttribute;
use Trellis\EntityType\Attribute\TextList\TextListAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class ProfileType extends EmbeddedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Profile',
            [
                new TextAttribute('nickname', $this, [], $parent_attribute),
                new TextAttribute('alias', $this, [], $parent_attribute),
                new TextListAttribute('tags', $this, [], $parent_attribute),
                new EntityListAttribute(
                    'badges',
                    $this,
                    [
                        'entity_types' => [ BadgeType::CLASS ]
                    ],
                    $parent_attribute
                ),
                new ReferenceListAttribute(
                    'memberships',
                    $this,
                    [
                        'entity_types' => [ MembershipType::CLASS ]
                    ],
                    $parent_attribute
                )
            ],
            [],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Profile::CLASS;
    }
}
