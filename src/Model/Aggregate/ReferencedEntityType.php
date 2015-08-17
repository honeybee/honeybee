<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Runtime\ReferencedEntityTypeInterface;
use Trellis\Common\OptionsInterface;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute;

// @todo we might want to inherit from Honeybee\Entity here, instead of EmbeddedEntityType
abstract class ReferencedEntityType extends EmbeddedEntityType implements ReferencedEntityTypeInterface
{
    const OPTION_IDENTIFYING_ATTRIBUTE_NAME = 'identifying_attribute';

    const OPTION_REFERENCED_TYPE_CLASS = 'referenced_type';

    public function __construct(
        $name,
        array $attributes = [],
        OptionsInterface $options = null,
        EntityTypeInterface $parent = null,
        AttributeInterface $parent_attribute = null
    ) {
        parent::__construct($name, $attributes, $options, $parent, $parent_attribute);

        if (!$this->hasOption(self::OPTION_IDENTIFYING_ATTRIBUTE_NAME)) {
            throw new RuntimeException(
                sprintf('Missing expected option "%s"', self::OPTION_IDENTIFYING_ATTRIBUTE_NAME)
            );
        }

        if (!$this->hasOption(self::OPTION_REFERENCED_TYPE_CLASS)) {
            throw new RuntimeException(
                sprintf('Missing expected option "%s"', self::OPTION_REFERENCED_TYPE_CLASS)
            );
        }
    }

    public function getReferencedAttributeName()
    {
        return $this->getOption(self::OPTION_IDENTIFYING_ATTRIBUTE_NAME);
    }

    public function getReferencedTypeClass()
    {
        return $this->getOption(self::OPTION_REFERENCED_TYPE_CLASS);
    }

    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
            [
                'referenced_identifier' => new TextAttribute(
                    'referenced_identifier',
                    $this,
                    [],
                    $this->getParentAttribute()
                )
            ]
        );
    }
}
