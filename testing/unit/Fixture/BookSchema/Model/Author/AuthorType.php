<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author;

use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\Embed\HighlightType;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\Text\TextAttribute;
use Workflux\StateMachine\StateMachineInterface;

class AuthorType extends AggregateRootType
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

    public function getEntityImplementor()
    {
        return Author::CLASS;
    }
}
