<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Author;

use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;
use Trellis\EntityType\Attribute\Email\EmailAttribute;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\ReferenceList\ReferenceListAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class AuthorType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Author',
            [
                new TextAttribute('firstname', $this, [ 'mandatory' => true, 'min_length' => 2 ]),
                new TextAttribute('lastname', $this, [ 'mandatory' => true ]),
                new EmailAttribute('email', $this, [ 'mandatory' => true ]),
                new TextAttribute('blurb', $this, [ 'default_value' =>  'the grinch' ]),
                new EntityListAttribute(
                    'products',
                    $this,
                    [
                        'inline_mode' => true,
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\BookSchema\\Projection\\Author\\Embed\\HighlightType',
                        ]
                    ]
                ),
                new ReferenceListAttribute(
                    'books',
                    $this,
                    [
                        'inline_mode' => true,
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\BookSchema\\Projection\\Author\\Reference\\BookType',
                        ]
                    ]
                )
            ]
        );
    }

    public function getEntityImplementor()
    {
        return Author::CLASS;
    }
}
