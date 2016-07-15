<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Team\TeamType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class MembershipType extends ReferencedEntityType
{
    /**
     * @param AttributeInterface|null $parent_attribute
     */
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Membership',
            [
                new TextAttribute('name', $this, [ 'mirrored' => true ], $parent_attribute)
            ],
            [
                'referenced_type' => TeamType::CLASS,
                'referenced_type_prefix' => 'team',
                'identifying_attribute' => 'identifier'
            ],
            $parent_attribute
        );
    }

    /**
     * @return string
     */
    public function getEntityImplementor()
    {
        return Membership::CLASS;
    }
}
