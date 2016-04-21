<?php

namespace Honeybee\Tests\Projection\Fixtures\Author;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
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
                'lastname' => new Text('lastname', $this, [ 'mandatory' => true ]),
                'blurb' => new Text('blurb', $this, [ 'default_value' =>  'the grinch' ]),
                'products' => new EmbeddedEntityListAttribute(
                    'products',
                    $this,
                    [
                        'inline_mode' => true,
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Projection\\Fixtures\\Author\\Embed\\HighlightType',
                        ]
                    ]
                ),
                'books' => new EntityReferenceListAttribute(
                    'books',
                    $this,
                    [
                        'inline_mode' => true,
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Projection\\Fixtures\\Author\\Reference\\BookType',
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
