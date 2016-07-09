<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model\Player\Embed;

use Honeybee\Model\Aggregate\EmbeddedEntityType;
use Trellis\Common\Options;
use Trellis\Runtime\EntityTypeInterface;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;

class BadgeType extends EmbeddedEntityType
{
    public function __construct(EntityTypeInterface $parent = null, AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Badge',
            [
                new Text('award', $this, [], $parent_attribute)
            ],
            new Options,
            $parent,
            $parent_attribute
        );
    }

    public function getEntityImplementor()
    {
        return Badge::CLASS;
    }
}
