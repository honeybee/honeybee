<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Team\TeamType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class ClanType extends ReferencedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Clan',
            [
                new TextAttribute('name', $this, [ 'mirrored' => true ], $parent_attribute)
            ],
            [
                'referenced_type' => TeamType::CLASS,
                'referenced_type_prefix' => 'team',
                'identifying_attribute' => 'identifier',
            ],
            [],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Clan::CLASS;
    }
}
