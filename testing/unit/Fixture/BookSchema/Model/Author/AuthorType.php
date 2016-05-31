<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author;

use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\Embed\HighlightType;
use Workflux\StateMachine\StateMachineInterface;

class AuthorType extends AggregateRootType
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
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\BookSchema\\Model\\Author\\Embed\\HighlightType',
                        ]
                    ]
                ),
                new EntityReferenceListAttribute(
                    'books',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Fixture\\BookSchema\\Model\\Author\\Reference\\BookType',
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
