<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Player\Reference;

use Honeybee\Model\Aggregate\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Model\Team\TeamType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class TeamType extends ReferencedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Team',
            [
                new TextAttribute('name', $this, [], $parent_attribute)
            ],
            [
                'referenced_type' => TeamType::CLASS,
                'identifying_attribute' => 'identifier'
            ],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
