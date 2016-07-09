<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Team\TeamType;

class TeamType extends ReferencedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Team',
            [
                new Text('name', $this, [ 'mirrored' => true ], $parent_attribute)
            ],
            [
                'referenced_type' => TeamType::CLASS,
                'identifying_attribute' => 'identifier',
            ],
            [],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
