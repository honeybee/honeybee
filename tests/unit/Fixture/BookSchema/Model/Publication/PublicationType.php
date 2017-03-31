<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publication;

use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;

class PublicationType extends AggregateRootType
{
    public function __construct()
    {
        parent::__construct(
            'Publication',
            [
                new IntegerAttribute('year', $this, []),
                new TextAttribute('description', $this, [])
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publication::CLASS;
    }
}
