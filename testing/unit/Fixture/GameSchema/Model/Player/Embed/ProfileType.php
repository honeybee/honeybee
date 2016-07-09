<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Player\Embed;

use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Model\EntityType;
use Honeybee\Tests\Fixture\GameSchema\Model\Player\Embed\BadgeType;
use Honeybee\Tests\Fixture\GameSchema\Model\Player\Reference\TeamType;

class ProfileType extends EmbeddedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Profile',
            [
                new Text('alias', $this, [], $parent_attribute),
                new TextListAttribute('tags', $this, [], $parent_attribute),
                new EntityReferenceListAttribute(
                    'teams',
                    $this,
                    [ 'entity_types' => [ TeamType::CLASS ] ],
                    $parent_attribute
                ),
                new EmbeddedEntityListAttribute(
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

    public function getEntityImplementor()
    {
        return Profile::CLASS;
    }
}
