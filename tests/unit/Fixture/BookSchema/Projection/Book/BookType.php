<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Book;

use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;
use Trellis\Runtime\Attribute\Text\TextAttribute;

class BookType extends ProjectionType
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
