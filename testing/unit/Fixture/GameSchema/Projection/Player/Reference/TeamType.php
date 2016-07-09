<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Player\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class TeamType extends ReferencedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Team',
            [
                new Text('name', $this, [ 'mirrored' => true ], $parent_attribute)
            ],
            new Options(
                [
                    'referenced_type' => '\\Honeybee\\Tests\\Fixture\\GameSchema\\Projection\\Team\\TeamType',
                    'identifying_attribute' => 'identifier',
                ]
            ),
            $parent,
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
