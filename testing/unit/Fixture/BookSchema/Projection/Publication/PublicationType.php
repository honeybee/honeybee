<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Publication;

use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;
use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;

class PublicationType extends ProjectionType
{
    public function __construct()
    {
        parent::__construct(
            'Publication',
            [
                new IntegerAttribute('year', $this, [ 'mandatory' => true ]),
                new TextAttribute('description', $this)
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publication::CLASS;
    }
}
