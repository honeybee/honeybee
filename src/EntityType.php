<?php

namespace Honeybee;

use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\HandlesFileInterface;
use Trellis\Runtime\Attribute\HandlesFileListInterface;
use Trellis\Runtime\Attribute\Uuid\UuidAttribute;
use Trellis\Runtime\EntityType as BaseEntityType;
use Honeybee\Common\ScopeKeyInterface;

abstract class EntityType extends BaseEntityType implements EntityTypeInterface, ScopeKeyInterface
{
    public function getMandatoryAttributes()
    {
        return $this->getAttributes()->filter(
            function($attribute) {
                return $attribute->getOption('mandatory', false);
            }
        );
    }

    public function getScopeKey()
    {
        $scope_key_parts = [];
        $type = $this;
        if ($parent_attribute = $this->getParentAttribute()) {
            while ($parent_attribute) {
                $scope_key_parts[] = $type->getPrefix();
                $scope_key_parts[] = $parent_attribute->getName();
                $type = $type->getParent();
                $parent_attribute = $type->getParentAttribute();
            }
            $scope_key_parts[] = $type->getPrefix();
            return implode('.', array_reverse($scope_key_parts));
        } else {
            return $type->getPrefix();
        }
    }

    public function isHierarchical()
    {
        return true === $this->getOption('is_hierarchical');
    }

    /**
     * Returns the default attributes that are initially added to a aggregate_type upon creation.
     *
     * @return array A list of AttributeInterface implementations.
     */
    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
            [ 'identifier' => new UuidAttribute('identifier', $this, [], $this->getParentAttribute()) ]
        );
    }

    /**
     * Returns the attributes of the current entity type (and its embedded entities)
     * that are capable of handling file properties (a location, mimetype, extension).
     *
     * @see HandlesFileListInterface
     * @see HandlesFileInterface
     *
     * @return array with attribute_path => attribute
     */
    public function getFileHandlingAttributes()
    {
        $attributes = [];

        foreach ($this->getAttributes() as $attribute_name => $attribute) {
            if ($attribute instanceof HandlesFileListInterface) {
                $attributes[$attribute->getPath()] = $attribute;
            } elseif ($attribute instanceof HandlesFileInterface) {
                $attributes[$attribute->getPath()] = $attribute;
            } elseif ($attribute instanceof EmbeddedEntityListAttribute) {
                foreach ($attribute->getEmbeddedEntityTypeMap() as $embedded_entity_type) {
                    $attributes = array_merge($attributes, $embedded_entity_type->getFileHandlingAttributes());
                }
            } else {
                // not an attribute that handles files
            }
        }

        return $attributes;
    }
}
