<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Runtime\Attribute\AttributeMap;
use Trellis\Runtime\Attribute\Uuid\UuidAttribute;

abstract class EmbeddedEntityType extends EntityType
{
    /**
     * Returns the default attributes that are initially added to a aggregate_type upon creation.
     *
     * @return AttributeMap A map of AttributeInterface implementations.
     */
    public function getDefaultAttributes()
    {
        $default_attributes = [
            new UuidAttribute('identifier', $this, [], $this->getParentAttribute())
        ];

        $default_attributes_map = new AttributeMap($default_attributes);
        return parent::getDefaultAttributes()->append($default_attributes_map);
    }
}
