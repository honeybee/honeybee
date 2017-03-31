<?php

return [
    // payload with no embeds or references
    [
        'payload' => [
            'author' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'email' => 'test@honeybee.com',
                'products' => [],
                'books' => []
            ]
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'email' => 'test@honeybee.com'
            ],
            'aggregate_root_type' => 'honeybee_cmf.aggregate_fixtures.author',
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
                'email' => 'test@honeybee.com',
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
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'email' => 'test@honeybee.com'
            ],
            'aggregate_root_type' => 'honeybee_cmf.aggregate_fixtures.author',
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
                'email' => 'test@honeybee.com',
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
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => '',
                'email' => 'test@honeybee.com'
            ],
            'aggregate_root_type' => 'honeybee_cmf.aggregate_fixtures.author',
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
                'email' => 'test@honeybee.com',
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
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand',
            'values' => [
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'email' => 'test@honeybee.com'
            ],
            'aggregate_root_type' => 'honeybee_cmf.aggregate_fixtures.author',
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
