<?php

namespace Honeybee\Projection;

use Honeybee\EntityType;
use Honeybee\Common\Util\StringToolkit;
use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Trellis\Runtime\Attribute\KeyValueList\KeyValueListAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Trellis\Runtime\Attribute\Timestamp\TimestampAttribute;
use Trellis\Runtime\Attribute\Uuid\UuidAttribute;
use Trellis\Runtime\Entity\EntityInterface;

abstract class ProjectionType extends EntityType implements ProjectionTypeInterface
{
    public function getVendor()
    {
        return $this->getOption('vendor', '');
    }

    public function getPackage()
    {
        return $this->getOption('package', '');
    }

    public function getPrefix()
    {
        return sprintf(
            '%s.%s.%s',
            strtolower($this->getVendor()),
            StringToolkit::asSnakeCase($this->getPackage()),
            StringToolkit::asSnakeCase($this->getName())
        );
    }

    /**
     * Creates a new Resource instance.
     *
     * @param array $data Optional data for initial hydration.
     * @param EntityInterface $parent_entity
     * @param boolean $apply_default_values
     *
     * @return ProjectionInterface
     *
     * @throws InvalidTypeException
     */
    public function createEntity(array $data = [], EntityInterface $parent_entity = null, $apply_default_values = false)
    {
        return parent::createEntity($data, $parent_entity, true);
    }

    /**
     * Returns the default attributes that are initially added to a aggregate_root_type upon creation.
     *
     * @return array A list of AttributeInterface implementations.
     */
    public function getDefaultAttributes()
    {
        $attributes = array_merge(
            parent::getDefaultAttributes(),
            [
                new TextAttribute('identifier', $this),
                new IntegerAttribute('revision', $this, [ 'default_value' => 0 ]),
                new UuidAttribute('uuid', $this),
                new IntegerAttribute('short_id', $this),
                new TextAttribute('language', $this, [ 'default_value' => 'de_DE' ]),
                new IntegerAttribute('version', $this, [ 'default_value' => 1 ]),
                new TimestampAttribute('created_at', $this, [ 'default_value' => 'now' ]),
                new TimestampAttribute('modified_at', $this, [ 'default_value' => 'now' ]),
                new TextAttribute('workflow_state', $this),
                new KeyValueListAttribute('workflow_parameters', $this),
                new KeyValueListAttribute('metadata', $this)
            ]
        );

        if ($this->isHierarchical()) {
            $attributes[] = new TextAttribute('parent_node_id', $this);
            $attributes[] = new TextAttribute('materialized_path', $this);
        }

        return $attributes;
    }
}
