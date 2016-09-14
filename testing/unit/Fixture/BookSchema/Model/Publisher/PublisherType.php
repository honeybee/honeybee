<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publisher;

use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Trellis\Runtime\Attribute\Text\TextAttribute;

class PublisherType extends AggregateRootType
{
    public function __construct()
    {
        parent::__construct(
            'Publisher',
            [
                new TextAttribute('name', $this, [ 'mandatory' => true ]),
                new TextAttribute('description', $this)
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publisher::CLASS;
    }
}
