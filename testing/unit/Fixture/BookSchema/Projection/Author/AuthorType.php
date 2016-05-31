<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Author;

use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Honeybee\Tests\Fixture\BookSchema\Projection\ProjectionType;
use Workflux\StateMachine\StateMachineInterface;

class AuthorType extends ProjectionType
{
    public function __construct(StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct(
            'Author',
            [
                new Text('firstname', $this, [ 'mandatory' => true ]),
                new Text('lastname', $this, [ 'mandatory' => true ]),
                new Text('blurb', $this, [ 'default_value' =>  'the grinch' ]),
                new EmbeddedEntityListAttribute(
                    'products',
                    $this,
                    [
                        'inline_mode' => true,
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\BookSchema\\Projection\\Author\\Embed\\HighlightType',
                        ]
                    ]
                ),
                new EntityReferenceListAttribute(
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

    public static function getEntityImplementor()
    {
        return Author::CLASS;
    }
}
