<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Book;

use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Trellis\Runtime\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class BookType extends AggregateRootType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Book',
            [
                new TextAttribute('title', $this, [ 'mandatory' => true ]),
                new TextAttribute('description', $this)
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Book::CLASS;
    }
}
