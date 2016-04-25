<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Publication;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Integer\IntegerAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Trellis\Runtime\Attribute\Type\ReferenceCollection;
use Honeybee\Tests\Model\Aggregate\Fixtures\EntityType;
use Workflux\StateMachine\StateMachineInterface;

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
