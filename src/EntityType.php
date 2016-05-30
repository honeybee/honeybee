<?php

namespace Honeybee;

use Trellis\Common\Object;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\HandlesFileInterface;
use Trellis\Runtime\Attribute\HandlesFileListInterface;
use Trellis\Runtime\Attribute\Uuid\UuidAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Trellis\Runtime\EntityType as BaseEntityType;
use Trellis\Runtime\Entity\EntityReferenceInterface;
use Trellis\Runtime\ReferencedEntityTypeInterface;
use Honeybee\Common\ScopeKeyInterface;

abstract class EntityType extends BaseEntityType implements EntityTypeInterface, ScopeKeyInterface
{
    public function getMandatoryAttributes()
    {
        return $this->getAttributes()->filter(
            function ($attribute) {
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
            [ new UuidAttribute('identifier', $this, [], $this->getParentAttribute()) ]
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

    /**
     * Recursively mirror values from the provided entity
     * @param EntityInterface $source_entity
     * @param EntityInterface $target_entity
     *
     * @return EntityInterface
     */
    public function createMirroredEntity(EntityInterface $source_entity, EntityInterface $target_entity = null)
    {
        // compile non-list attribute values from the target entity if available
        if ($target_entity) {
            foreach ($this->getAttributes() as $attribute) {
                if (!$attribute instanceof EmbeddedEntityListAttribute) {
                    $attribute_name = $attribute->getName();
                    $attribute_value = $target_entity->getValue($attribute_name);
                    $mirrored_values[$attribute_name] = $attribute_value instanceof Object
                        ? $attribute_value->toArray()
                        : $attribute_value;
                }
            }
        }

        // override default mirrored values
        $mirrored_values['@type'] = $source_entity->getType()->getPrefix();
        $mirrored_values['identifier'] = $source_entity->getIdentifier();
        if ($source_entity instanceof EntityReferenceInterface) {
            $mirrored_values['referenced_identifier'] = $source_entity->getReferencedIdentifier();
        }

        // collate the required mirrored attributes map
        $mirrored_attributes_map = $this->collateAttributes(
            function (AttributeInterface $attribute) {
                return (bool)$attribute->getOption('mirrored', false) === true;
            }
        );

        // extract our reference path which may be aliased
        $target_prefix = $this instanceof ReferencedEntityTypeInterface
            ? $this->getPrefix()
            : $source_entity->getType()->getPrefix();
        $path_parts = explode('.', $target_prefix);
        $type_prefix = end($path_parts);

        // iterate the source attributes and extract the required mirrored values
        foreach ($mirrored_attributes_map->getKeys() as $mirrored_attribute_path) {
            // @todo possible risk of path name collision in greedy regex
            $mirrored_attribute_path = preg_replace('#([a-z]+\.)+'.$type_prefix.'\.#', '', $mirrored_attribute_path);
            $mirrored_attr_name = explode('.', $mirrored_attribute_path)[0];
            $mirrored_attribute = $this->getAttribute($mirrored_attr_name);
            $source_attribute_name = $mirrored_attribute->getOption('attribute_alias', $mirrored_attr_name);
            $source_attribute_value = $source_entity->getValue($source_attribute_name);
            if ($mirrored_attribute instanceof EmbeddedEntityListAttribute) {
                foreach ($source_attribute_value as $position => $source_embedded_entity) {
                    // skip entity mirroring if values already exist since we may traverse over paths repeatedly
                    if (!isset($mirrored_values[$mirrored_attr_name][$position])) {
                        $source_embed_prefix = $source_embedded_entity->getType()->getPrefix();
                        $mirrored_embed_type = $mirrored_attribute instanceof EntityReferenceListAttribute
                            ? $mirrored_attribute->getEmbeddedTypeByReferencedPrefix($source_embed_prefix)
                            : $mirrored_attribute->getEmbeddedTypeByPrefix($source_embed_prefix);
                        if ($mirrored_embed_type) {
                            $target_embedded_entity = $target_entity
                                ? $target_entity->getValue($mirrored_attr_name)
                                    ->getEntityByIdentifier($source_embedded_entity->getIdentifier())
                                : null;
                            $mirrored_embedded_entity = $mirrored_embed_type->createEntity(
                                $mirrored_embed_type->createMirroredEntity(
                                    $source_embedded_entity,
                                    $target_embedded_entity
                                )->toArray(),
                                $source_embedded_entity->getParent()
                            );
                            $mirrored_values[$mirrored_attr_name][$position] = $mirrored_embedded_entity->toArray();
                        }
                    }
                }
            } else {
                $mirrored_values[$mirrored_attr_name] = $source_attribute_value instanceof Object
                    ? $source_attribute_value->toArray()
                    : $source_attribute_value;
            }
        }

        return $this->createEntity($mirrored_values, $source_entity->getParent());
    }
}
