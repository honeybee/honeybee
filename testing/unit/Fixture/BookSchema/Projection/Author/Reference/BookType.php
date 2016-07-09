<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Author\Reference;

use Honeybee\EntityTypeInterface;
use Honeybee\Projection\ReferencedEntityType;
use Honeybee\Tests\Fixture\BookSchema\Model\Book\BookType;
use Trellis\EntityType\Attribute\AttributeInterface;

class BookType extends ReferencedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Book',
            [],
            [
                'referenced_type' => BookType::CLASS,
                'identifying_attribute' => 'identifier',
            ],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Book::CLASS;
    }
}
