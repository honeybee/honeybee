<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection\Game\Embed;

use Honeybee\Projection\EmbeddedEntityType;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\Integer\IntegerAttribute;

class ChallengeType extends EmbeddedEntityType
{
    /**
     * @param AttributeInterface|null $parent_attribute
     */
    public function __construct(AttributeInterface $parent_attribute = null)
    {
        parent::__construct(
            'Challenge',
            [ new IntegerAttribute('attempts', $this, [], $parent_attribute) ],
            [],
            $parent_attribute
        );
    }

    /**
     * @return string
     */
    public function getEntityImplementor()
    {
        return Challenge::CLASS;
    }
}
