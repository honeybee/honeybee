<?php

namespace Honeybee\Projection;

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
        $default_attributes_map = new AttributeMap(
            [ new UuidAttribute('identifier', $this, [], $this->getParentAttribute()) ]
        );

        return parent::getDefaultAttributes()->append($default_attributes_map);
    }
}
