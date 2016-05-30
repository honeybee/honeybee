<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Author;

use Trellis\Common\Options;
use Trellis\Runtime\Attribute\Text\TextAttribute as Text;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Honeybee\Tests\Model\Aggregate\Fixtures\EntityType;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\Embed\HighlightType;
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
                new Text('firstname', $this, [ 'mandatory' => true ]),
                new Text('lastname', $this, [ 'mandatory' => true ]),
                new Text('blurb', $this, [ 'default_value' =>  'the grinch' ]),
                new EmbeddedEntityListAttribute(
                    'products',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Model\\Aggregate\\Fixtures\\Author\\Embed\\HighlightType',
                        ]
                    ]
                ),
                new EntityReferenceListAttribute(
                    'books',
                    $this,
                    [
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Model\\Aggregate\\Fixtures\\Author\\Reference\\BookType',
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
