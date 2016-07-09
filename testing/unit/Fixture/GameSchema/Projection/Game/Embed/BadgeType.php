<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game\Embed;

use Honeybee\Projection\EmbeddedEntityType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Text\TextAttribute;

class BadgeType extends EmbeddedEntityType
{
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Badge',
            [
                new TextAttribute('award', $this, [ 'mirrored' => true ], $parent_attribute)
            ],
            [],
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Badge::CLASS;
    }
}
