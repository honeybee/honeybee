<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author\Reference;

use Honeybee\EntityTypeInterface;
use Honeybee\Model\Aggregate\ReferencedEntityType;
use Trellis\EntityType\Attribute\AttributeInterface;

class BookType extends ReferencedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Book',
            [],
            [
                'referenced_type' => '\\Honeybee\\Tests\\Model\\Aggregate\\Fixture\\Book\\BookType',
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
