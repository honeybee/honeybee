<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Author\Reference;

use Honeybee\Model\Aggregate\ReferencedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;

class BookType extends ReferencedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Book',
            [],
            new Options(
                [
                    'referenced_type' => '\\Honeybee\\Tests\\Model\\Aggregate\\Fixtures\\Book\\BookType',
                    'identifying_attribute' => 'identifier',
                ]
            ),
            $parent,
            $parent_attribute
        );
    }

    public static function getEntityImplementor()
    {
        return Book::CLASS;
    }
}
