<?php

namespace Honeybee\Model\Aggregate;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Trellis\EntityType\Attribute\AttributeMap;
use Trellis\EntityType\Attribute\Integer\IntegerAttribute;
use Trellis\EntityType\Attribute\KeyValueList\KeyValueListAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Trellis\EntityType\Attribute\Uuid\UuidAttribute;
use Trellis\Entity\EntityInterface;

abstract class AggregateRootType extends EntityType implements AggregateRootTypeInterface
{
    public function getVendor()
    {
        return $this->getOption('vendor', '');
    }

    public function getPackage()
    {
        return $this->getOption('package', '');
    }

    public function getPackagePrefix()
    {
        return sprintf(
            '%s.%s',
            strtolower($this->getVendor()),
            StringToolkit::asSnakeCase($this->getPackage())
        );
    }

    public function getPrefix()
    {
        return sprintf(
            '%s.%s',
            $this->getPackagePrefix(),
            StringToolkit::asSnakeCase($this->getName())
        );
    }

    /**
     * Creates a new AggregateRoot instance.
     * The parent (EntityType) method is overriden to adhere the rules for a new aggregate-root:
     * no initial state and being the root-entity also no parent.
     *
     * @param array $data Optional data for initial hydration (is dropped by this class).
     * @param EntityInterface $parent_entity (also dropped)
     *
     * @return EntityInterface
     *
     * @throws InvalidTypeException
     */
    public function createEntity(array $data = [], EntityInterface $parent_entity = null)
    {
        if (!empty($data)) {
            throw new RuntimeError(
                'An aggregate root can only be reconstituted from its historical event stream ' .
                'or populated via an appropriate command, so it is not possible to create an ' .
                'aggregate root explicitly with state.'
            );
        }

        $implementor = $this->getEntityImplementor();
        if (!class_exists($implementor, true)) {
            throw new RuntimeError(
                sprintf(
                    'Unable to resolve the given aggregate-root implementor "%s" to an existing class.',
                    $implementor
                )
            );
        }

        return new $implementor($this, $this->getWorkflowStateMachine());
    }

    /**
     * Returns the default attributes that are initially added to a aggregate_root_type upon creation.
     *
     * @return array A list of AttributeInterface implementations.
     */
    public function getDefaultAttributes()
    {
        $default_attributes = [
            new TextAttribute('identifier', $this),
            new IntegerAttribute('revision', $this, [ 'default_value' => 0 ]),
            new UuidAttribute('uuid', $this, [ 'default_value' => 'auto_gen' ]),
            new TextAttribute('language', $this, [ 'default_value' => 'de_DE' ]),
            new IntegerAttribute('version', $this, [ 'default_value' => 1 ]),
            new TextAttribute('workflow_state', $this),
            new KeyValueListAttribute('workflow_parameters', $this)
        ];

        if ($this->isHierarchical()) {
            $default_attributes[] = new TextAttribute('parent_node_id', $this);
        }

        $default_attributes_map = new AttributeMap($default_attributes);

        return parent::getDefaultAttributes()->append($default_attributes_map);
    }
}
