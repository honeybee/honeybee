<?php

namespace Honeybee\Tests\Projection\Fixtures\Publication;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Honeybee\Tests\Projection\Fixtures\EntityType;

class PublicationType extends EntityType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        parent::__construct('Publication', $state_machine);
    }

    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
            [
                'year' => new IntegerAttribute('year', [ 'mandatory' => true ]),
                'description' => new TextAttribute('description')
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Publication::CLASS;
    }
}
