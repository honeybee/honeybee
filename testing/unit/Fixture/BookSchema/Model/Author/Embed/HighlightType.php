<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author\Embed;

use Honeybee\EntityTypeInterface;
use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class HighlightType extends EmbeddedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Highlight',
            [
                new TextAttribute('title', $this, [ 'mandatory' => true ], $parent_attribute),
                new TextAttribute('description', $this, [], $parent_attribute)
            ],
            [],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Highlight::CLASS;
    }
}
