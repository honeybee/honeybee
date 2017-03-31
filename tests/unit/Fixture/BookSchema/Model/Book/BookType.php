<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Book;

use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Trellis\Runtime\Attribute\Text\TextAttribute;

class BookType extends AggregateRootType
{
    public function __construct()
    {
        parent::__construct(
            'Book',
            [
                new TextAttribute('title', $this, [ 'mandatory' => true ]),
                new TextAttribute('description', $this)
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Book::CLASS;
    }
}
