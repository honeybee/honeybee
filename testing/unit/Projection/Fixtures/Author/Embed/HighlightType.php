<?php

namespace Honeybee\Tests\Projection\Fixtures\Author\Embed;

use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\EntityType;

class HighlightType extends EntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Highlight',
            [
                new Text('title', $this, [], $parent_attribute),
                new Text('description', $this, [], $parent_attribute)
            ],
            new Options([]),
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return '\\Honeybee\\Tests\\Projection\\Fixtures\\Author\\Embed\\Highlight';
    }
}
