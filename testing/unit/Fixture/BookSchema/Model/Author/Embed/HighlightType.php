<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author\Embed;

use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Trellis\Runtime\EntityTypeInterface;

class HighlightType extends EmbeddedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Highlight',
            [
                new TextAttribute('title', $this, [], $parent_attribute),
                new TextAttribute('description', $this, [], $parent_attribute),
            ],
            new Options([]),
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return Highlight::CLASS;
    }
}
