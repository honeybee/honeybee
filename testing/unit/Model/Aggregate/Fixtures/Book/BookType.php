<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Book;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Model\Aggregate\Fixtures\EntityType;
use Workflux\StateMachine\StateMachineInterface;

class BookType extends EntityType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        parent::__construct('Book', $state_machine);
    }

    public function getDefaultAttributes()
    {
        return array_merge(
            parent::getDefaultAttributes(),
            [
                new Text('title', $this, [ 'mandatory' => true ]),
                new Text('description', $this)
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Book::CLASS;
    }
}
