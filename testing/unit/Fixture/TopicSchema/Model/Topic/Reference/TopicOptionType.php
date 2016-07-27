<?php

namespace Honeybee\Tests\Fixture\TopicSchema\Model\Topic\Reference;

use Honeybee\Model\Aggregate\ReferencedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\EntityTypeInterface;

class TopicOptionType extends ReferencedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'TopicOption',
            [],
            new Options(
                [
                    'referenced_type' => '\\Honeybee\\Tests\\Fixture\\TopicSchema\\Model\\TopicOption\\TopicOptionType',
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
