<?php

namespace Honeybee\Tests\Fixture\TopicSchema\Projection\Topic\Reference;

use Honeybee\Projection\ReferencedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Trellis\Runtime\EntityTypeInterface;

class TopicOptionType extends ReferencedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'TopicOption',
            [
                new TextAttribute('title', $this, [ 'mirrored' => true ], $parent_attribute)
            ],
            new Options(
                [
                    'referenced_type' =>
                        '\\Honeybee\\Tests\\Fixture\\TopicSchema\\Projection\\TopicOption\\TopicOptionType',
                    'identifying_attribute' => 'identifier'
                ]
            ),
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return TopicOption::CLASS;
    }
}
