<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Player\Reference;

use Honeybee\Model\Aggregate\ReferencedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class ClanType extends ReferencedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Clan',
            [
                new Text('name', $this, [], $parent_attribute)
            ],
            new Options(
                [
                    'referenced_type' => '\\Honeybee\\Tests\\Fixture\\GameSchema\\Model\\Team\\TeamType',
                    'referenced_type_prefix' => 'team',
                    'identifying_attribute' => 'identifier'
                ]
            ),
            $parent,
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Clan::CLASS;
    }
}
