<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Team\TeamType as ReferencedTeamType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class TeamType extends ReferencedEntityType
{
    /**
     * @param AttributeInterface|null $parent_attribute
     */
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Team',
            [ new TextAttribute('name', $this, [ 'mirrored' => true ]) ],
            [
                'referenced_type' => ReferencedTeamType::CLASS,
                'identifying_attribute' => 'identifier',
            ],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
