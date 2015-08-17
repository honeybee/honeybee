<?php

namespace Honeybee\Projection;

use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Trellis\Runtime\Attribute\KeyValueList\KeyValueListAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Trellis\Runtime\Attribute\Timestamp\TimestampAttribute;
use Trellis\Runtime\Attribute\Uuid\UuidAttribute;
use Trellis\Runtime\Entity\EntityInterface;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\EntityType;
use Honeybee\Projection\WorkflowSubject;
use Workflux\StateMachine\StateMachine;

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
                'identifier' => new TextAttribute('identifier', $this),
                'revision' => new IntegerAttribute('revision', $this, [ 'default_value' => 0 ]),
                'uuid' => new UuidAttribute('uuid', $this),
                'short_id' => new IntegerAttribute('short_id', $this),
                'language' => new TextAttribute('language', $this, [ 'default_value' => 'de_DE' ]),
                'version' => new IntegerAttribute('version', $this, [ 'default_value' => 1 ]),
                'created_at' => new TimestampAttribute('created_at', $this, [ 'default_value' => 'now' ]),
                'modified_at' => new TimestampAttribute('modified_at', $this, [ 'default_value' => 'now' ]),
                'workflow_state' => new TextAttribute('workflow_state', $this),
                'workflow_parameters' => new KeyValueListAttribute('workflow_parameters', $this)
            ]
        );

        if ($this->isHierarchical()) {
            $attributes['parent_node_id'] = new TextAttribute('parent_node_id', $this);
            $attributes['materialized_path'] = new TextAttribute('materialized_path', $this);
        }

        return $attributes;
    }
}
