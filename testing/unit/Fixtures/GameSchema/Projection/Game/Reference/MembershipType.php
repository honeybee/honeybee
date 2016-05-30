<?php

namespace Honeybee\Tests\Fixtures\GameSchema\Projection\Game\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixtures\GameSchema\Projection\ProjectionType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class MembershipType extends ReferencedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Membership',
            [
                new Text('name', $this, [ 'mirrored' => true ], $parent_attribute)
            ],
            new Options(
                [
                    'referenced_type' => ProjectionType::NAMESPACE_PREFIX . 'Team\\TeamType',
                    'referenced_type_prefix' => 'team',
                    'identifying_attribute' => 'identifier'
                ]
            ),
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return Membership::CLASS;
    }
}
