<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Game\Reference;

use Honeybee\Model\Aggregate\ReferencedEntityType;
use Honeybee\Tests\Fixture\GameSchema\Model\Team\TeamType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class MembershipType extends ReferencedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Membership',
            [
                new TextAttribute('name', $this, [], $parent_attribute)
            ],
            [
                'referenced_type' => TeamType::CLASS,
                'referenced_type_prefix' => 'team',
                'identifying_attribute' => 'identifier'
            ],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Membership::CLASS;
    }
}
