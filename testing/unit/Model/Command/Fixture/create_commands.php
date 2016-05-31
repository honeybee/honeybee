<?php

return [
    // empty payload
    [
      'payload' => [
          'author' => []
      ],
      'expected_command' => [
          '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor\CreateAuthorCommand',
          'values' => [],
          'aggregate_root_type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType',
          'embedded_entity_commands' => [],
          'metadata' => []
      ]
    ],
    // payload with no embeds or references
    [
        'payload' => [
            'author' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'products' => [],
                'books' => []
            ]
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh'
            ],
            'aggregate_root_type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType',
            'embedded_entity_commands' => [],
            'metadata' => []
        ]
    ],
    // payload with embeds only
    [
        'payload' => [
            'author' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'products' => [
                    [
                        '@type' => 'highlight',
                        'title' => 'Purlitzer Prize',
                        'description' => 'The greatest author ever.'
                    ]
                ]
            ]
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh'
            ],
            'aggregate_root_type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType',
            'embedded_entity_commands' => [
                [
                    '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'embedded_entity_type' => 'highlight',
                    'parent_attribute_name' => 'products',
                    'values' => [
                        'title' => 'Purlitzer Prize',
                        'description' => 'The greatest author ever.'
                    ],
                    'embedded_entity_commands' => [],
                    'position' => 0,
                    'metadata' => []
                ]
            ],
            'metadata' => []
        ]
    ],
    // payload with embeds and references and empty values
    [
        'payload' => [
            'author' => [
                'firstname' => 'Amitav',
                'lastname' => '',
                'products' => [
                    [
                        '@type' => 'highlight',
                        'title' => '',
                        'description' => 'The greatest author ever.'
                    ]
                ],
                'books' => [
                    [
                        '@type' => 'book',
                        'referenced_identifier' => 'book1'
                    ],
                    [
                        '@type' => 'book',
                        'referenced_identifier' => 'book2'
                    ]
                ]
            ]
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => ''
            ],
            'aggregate_root_type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType',
            'embedded_entity_commands' => [
                [
                    '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'embedded_entity_type' => 'highlight',
                    'parent_attribute_name' => 'products',
                    'values' => [
                        'title' => '',
                        'description' => 'The greatest author ever.'
                    ],
                    'embedded_entity_commands' => [],
                    'position' => 0,
                    'metadata' => []
                ],
                [
                    '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'values' => [
                        'referenced_identifier' => 'book1'
                    ],
                    'embedded_entity_type' => 'book',
                    'parent_attribute_name' => 'books',
                    'embedded_entity_commands' => [],
                    'position' => 0,
                    'metadata' => []
                ],
                [
                    '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'values' => [
                        'referenced_identifier' => 'book2'
                    ],
                    'embedded_entity_type' => 'book',
                    'parent_attribute_name' => 'books',
                    'embedded_entity_commands' => [],
                    'position' => 1,
                    'metadata' => []
                ]
            ],
            'metadata' => []
        ]
    ],
    // payload with references only
    [
        'payload' => [
            'author' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'books' => [
                    [
                        '@type' => 'book',
                        'referenced_identifier' => 'book1'
                    ],
                    [
                        '@type' => 'book',
                        'referenced_identifier' => 'book2'
                    ]
                ]
            ]
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh'
            ],
            'aggregate_root_type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType',
            'embedded_entity_commands' => [
                [
                    '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'values' => [
                        'referenced_identifier' => 'book1'
                    ],
                    'embedded_entity_type' => 'book',
                    'parent_attribute_name' => 'books',
                    'embedded_entity_commands' => [],
                    'position' => 0,
                    'metadata' => []
                ],
                [
                    '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'values' => [
                        'referenced_identifier' => 'book2'
                    ],
                    'embedded_entity_type' => 'book',
                    'parent_attribute_name' => 'books',
                    'embedded_entity_commands' => [],
                    'position' => 1,
                    'metadata' => []
                ]
            ],
            'metadata' => []
        ]
    ]
];
