<?php

namespace Honeybee\Projection;

use Honeybee\EntityInterface;
use Trellis\Common\ObjectInterface;
use Honeybee\EntityType as BaseEntityType;
use Trellis\Runtime\ReferencedEntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Trellis\Runtime\Entity\EntityReferenceInterface;

abstract class EntityType extends BaseEntityType
{
    /**
     * Create a new entity from the recursively mirrorred values of a given source entity, while
     * optionally merging attribute values from a given reference entity.
     *
     * @param EntityInterface $source_entity
     * @param EntityInterface $reference_entity
     *
     * @return EntityInterface
     */
    public function createMirroredEntity(EntityInterface $source_entity, EntityInterface $reference_entity = null)
    {
        // compile non-list attribute values from the reference entity if available
        if ($reference_entity) {
            foreach ($this->getAttributes() as $attribute) {
                if (!$attribute instanceof EmbeddedEntityListAttribute) {
                    $attribute_name = $attribute->getName();
                    $attribute_value = $reference_entity->getValue($attribute_name);
                    $mirrored_values[$attribute_name] = $attribute_value instanceof ObjectInterface
                        ? $attribute_value->toArray()
                        : $attribute_value;
                }
            }
        }

        // override default mirrored values
        $mirrored_values['@type'] = $this instanceof ProjectionInterface
            ? $this->getVariantPrefix()
            : $source_entity->getType()->getPrefix();
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
        $reference_prefix = $this instanceof ReferencedEntityTypeInterface
            ? $this->getPrefix()
            : $source_entity->getType()->getPrefix();
        $path_parts = explode('.', $reference_prefix);
        $type_prefix = end($path_parts);

        // iterate the source attributes and extract the required mirrored values
        foreach ($mirrored_attributes_map->getKeys() as $mirrored_attribute_path) {
            // @todo possible risk of path name collision in greedy regex
            $mirrored_attribute_path = preg_replace('#([\w-]+\.)+'.$type_prefix.'\.#', '', $mirrored_attribute_path);
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
                            $reference_embedded_entity = $reference_entity
                            ? $reference_entity->getValue($mirrored_attr_name)
                                ->getEntityByIdentifier($source_embedded_entity->getIdentifier())
                                : null;
                            $mirrored_embedded_entity = $mirrored_embed_type->createEntity(
                                $mirrored_embed_type->createMirroredEntity(
                                    $source_embedded_entity,
                                    $reference_embedded_entity
                                )->toArray(),
                                $source_embedded_entity->getParent()
                            );
                            $mirrored_values[$mirrored_attr_name][$position] = $mirrored_embedded_entity->toArray();
                        }
                    }
                }
            } else {
                $mirrored_values[$mirrored_attr_name] = $source_attribute_value instanceof ObjectInterface
                    ? $source_attribute_value->toArray()
                    : $source_attribute_value;
            }
        }

        return $this->createEntity($mirrored_values, $source_entity->getParent());
    }
}
