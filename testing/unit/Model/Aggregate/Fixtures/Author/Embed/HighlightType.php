<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Author\Embed;

use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class HighlightType extends EmbeddedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Highlight',
            [
                new Text('title', $this, [], $parent_attribute),
                new Text('description', $this, [], $parent_attribute),
            ],
            new Options([]),
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return '\\Honeybee\\Tests\\Model\\Aggregate\\Fixtures\\Author\\Embed\\Highlight';
    }
}
