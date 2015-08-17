<?php

namespace Honeybee\Tests\Projection\Resource\Fixtures\Book;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\ResourceType;
use Trellis\Runtime\EntityTypeInterface;

class ParagraphType extends ResourceType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct('Paragraph', $parent, $parent_attribute);
    }

    protected function getDefaultAttributes()
    {
        return [
            'headline' => new Text('headline', $this, [ 'mandatory' => true ], $parent_attribute),
            'content' => new Text('content', $this, [], $parent_attribute)
        ];
    }

    protected function getEntityImplementor()
    {
        return Paragraph::CLASS;
    }
}
