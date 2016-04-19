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
                // keys required here because default attributes are not set into the attribute map properly
                'firstname' => new Text('firstname', $this, [ 'mandatory' => true ]),
                'lastname' => new Text('lastname', $this, [ 'mandatory' => true ]),
                'blurb' => new Text('blurb', $this, [ 'default_value' =>  'the grinch' ]),
                'products' => new EmbeddedEntityListAttribute(
                    'products',
                    $this,
                    [
                        'inline_mode' => true,
                        'entity_types' => [
                            '\\Honeybee\\Tests\\Model\\Aggregate\\Fixtures\\Author\\Embed\\HighlightType',
                        ]
                    ]
                ),
                'books' => new EntityReferenceListAttribute(
                    'books',
                    $this,
                    [
                        'inline_mode' => true,
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
