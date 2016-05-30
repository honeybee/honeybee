<?php

namespace Honeybee\Tests\Fixtures\GameSchema\Model\Player\Reference;

use Honeybee\Model\Aggregate\ReferencedEntityType;
use Honeybee\Tests\Fixtures\GameSchema\Model\EntityType;
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
                new Text('name', $this, [], $parent_attribute)
            ],
            new Options(
                [
                    'referenced_type' => EntityType::NAMESPACE_PREFIX . 'Team\\TeamType',
                    'identifying_attribute' => 'identifier'
                ]
            ),
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return Team::CLASS;
    }
}
