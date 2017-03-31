<?php

namespace Honeybee\Tests\Fixture\TopicSchema\Projection\TopicOption;

use Honeybee\Tests\Fixture\TopicSchema\Projection\ProjectionType;
use Trellis\Runtime\Attribute\Text\TextAttribute;

class TopicOptionType extends ProjectionType
{
    public function __construct()
    {
        parent::__construct(
            'TopicOption',
            [
                new TextAttribute('title', $this)
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return TopicOption::CLASS;
    }
}
