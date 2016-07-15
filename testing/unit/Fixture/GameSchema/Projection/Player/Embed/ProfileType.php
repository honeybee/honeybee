<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player\Embed;

use Honeybee\Projection\EmbeddedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Player\Reference\ClanType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Player\Reference\TeamType;
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
                new TextAttribute('alias', $this, [], $parent_attribute),
                new TextListAttribute('tags', $this, [], $parent_attribute),
                new ReferenceListAttribute(
                    'teams',
                    $this,
                    [ 'entity_types' => [ TeamType::CLASS, ClanType::CLASS ] ],
                    $parent_attribute
                ),
                new EntityListAttribute(
                    'badges',
                    $this,
                    [ 'entity_types' => [ BadgeType::CLASS ] ],
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
