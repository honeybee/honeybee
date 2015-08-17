<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Author;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Model\Aggregate\Fixtures\EntityType;
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
                'lastname' => new Text('lastname', $this, [ 'mandatory' => true ]),
                'blurb' => new Text('blurb', $this, [ 'default_value' =>  'the grinch' ])
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Author::CLASS;
    }
}
