<?php

namespace Honeybee\Tests\Projection\Fixtures\Author;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Projection\Fixtures\EntityType;
use Workflux\StateMachine\StateMachineInterface;

class AuthorType extends EntityType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        parent::__construct('Author', $state_machine);
    }

    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
            [
                'firstname' => new Text('firstname', $this, [ 'mandatory' => true ]),
                'lastname' => new Text('lastname', $this, [ 'mandatory' => true ])
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Author::CLASS;
    }
}
