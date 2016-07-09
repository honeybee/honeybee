<?php

namespace Honeybee\Model\Aggregate;

use Trellis\EntityType\Attribute\AttributeMap;
use Trellis\EntityType\Attribute\Uuid\UuidAttribute;

abstract class EmbeddedEntityType extends EntityType
{
    /**
     * Returns the default attributes that are initially added to a aggregate_type upon creation.
     *
     * @return AttributeMap A map of AttributeInterface implementations.
     */
    public function getDefaultAttributes()
    {
        $default_attributes = [ new UuidAttribute('identifier', $this, [], $this->getParentAttribute()) ];

        return parent::getDefaultAttributes()->append(new AttributeMap($default_attributes));
    }
}
