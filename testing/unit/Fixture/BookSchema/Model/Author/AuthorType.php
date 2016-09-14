<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author;

use Honeybee\Tests\Fixture\BookSchema\Model\AggregateRootType;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\Embed\HighlightType;
use Trellis\Runtime\Attribute\Email\EmailAttribute;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Trellis\Runtime\Attribute\Text\TextAttribute;

class AuthorType extends AggregateRootType
{
    public function __construct()
    {
        parent::__construct(
            'Author',
            [
                new TextAttribute('firstname', $this, [ 'mandatory' => true, 'min_length' => 2 ]),
                new TextAttribute('lastname', $this, [ 'mandatory' => true ]),
                new EmailAttribute('email', $this, [ 'mandatory' => true ]),
                new TextAttribute('blurb', $this, [ 'default_value' =>  'the grinch' ]),
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
