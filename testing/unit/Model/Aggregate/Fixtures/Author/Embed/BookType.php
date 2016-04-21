<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Author\Embed;

use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;

class BookType extends EmbeddedEntityType
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
        return '\\Honeybee\\Tests\\Model\\Aggregate\\Fixtures\\Author\\Reference\\Book';
    }
}
