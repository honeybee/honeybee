<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Book;

use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;
use Workflux\StateMachine\StateMachineInterface;

class BookType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;
        parent::__construct(
            'Book',
            [
                new Text('title', $this, [ 'mandatory' => true ]),
                new Text('description', $this),
                new EmbeddedEntityListAttribute(
                    'paragraphs',
                    $this,
                    [ EmbeddedEntityListAttribute::OPTION_ENTITY_TYPES => [ ParagraphType::CLASS ] ]
                )
            ]
        );
    }

    public static function getEntityImplementor()
    {
        return Book::CLASS;
    }
}
