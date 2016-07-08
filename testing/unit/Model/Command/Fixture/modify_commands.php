<?php

$aggregate_root_identifier = 'honeybee.fixtures.author-fa44c523-592f-404f-bcd5-00f04ff5ce61-de_DE-1';
$type_namespace_prefix = 'Honeybee\\Model\\Task\\ModifyAggregateRoot\\';

return [
    // payload with empty no embeds or references
    [
        'projection' => [
            'identifier' => $aggregate_root_identifier,
            'revision' => 1,
            'firstname' => 'Amitav',
            'lastname' => 'Gosh'
        ],
        'payload' => [
            'author' => [
                'firstname' => 'Vatima',
                'lastname' => 'Hsog'
            ],
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\ModifyAuthor\ModifyAuthorCommand',
            'values' => [
                'firstname' => 'Vatima',
                'lastname' => 'Hsog',
            ],
            'aggregate_root_identifier' => $aggregate_root_identifier,
            'aggregate_root_type' => 'honeybee-cmf.aggregate_fixtures.author',
            'known_revision' => 1,
            'embedded_entity_commands' => [],
            'metadata' => []
        ]
    ],
    // payload with updated embed and compensation
    [
        'projection' => [
            'identifier' => $aggregate_root_identifier,
            'revision' => 3,
            'firstname' => 'Amitav',
            'lastname' => 'Gosh',
            'products' => [
                [
                    '@type' => 'highlight',
                    'identifier' => 'e3b42f1d-5235-94ce-48d3-c2d09846c642',
                    'title' => 'Nobel Prize',
                    'description' => 'Not bad.'
                ],
                [
                    '@type' => 'highlight',
                    'identifier' => 'd8a77e2c-9329-49ec-84e7-a1d05946c447',
                    'title' => 'Purlitzer Prize',
                    'description' => 'The greatest author ever.'
                ]
            ],
            'books' => []
        ],
        'payload' => [
            'author' => [
                'identifier' => $aggregate_root_identifier,
                'firstname' => 'Amitav',
                'lastname' => 'OhMyGosh',
                'products' => [
                    [
                        '@type' => 'highlight',
                        'title' => 'Nobel Prize',
                        'description' => 'Not bad.'
                    ],
                    [
                        '@type' => 'highlight',
                        'identifier' => 'd8a77e2c-9329-49ec-84e7-a1d05946c447',
                        'title' => 'Darwin Award',
                        'description' => 'The greatest author ever.'
                    ]
                ],
                'books' => []
            ]
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\ModifyAuthor\ModifyAuthorCommand',
            'values' => [
                'lastname' => 'OhMyGosh'
            ],
            'aggregate_root_identifier' => $aggregate_root_identifier,
            'aggregate_root_type' => 'honeybee-cmf.aggregate_fixtures.author',
            'known_revision' => 3,
            'embedded_entity_commands' => [
                [
                    '@type' => $type_namespace_prefix . 'ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand',
                    'embedded_entity_identifier' => 'd8a77e2c-9329-49ec-84e7-a1d05946c447',
                    'embedded_entity_type' => 'highlight',
                    'parent_attribute_name' => 'products',
                    'values' => [
                        'title' => 'Darwin Award'
                    ],
                    'embedded_entity_commands' => [],
                    'position' => 1,
                    'metadata' => [],
                ]
            ],
            'metadata' => []
        ]
    ],
    // payload with added, removed and updated references and embeds
    [
        'projection' => [
            'identifier' => $aggregate_root_identifier,
            'revision' => 7,
            'firstname' => 'Amitav',
            'lastname' => 'Gosh',
            'products' => [
                [
                    '@type' => 'highlight',
                    'identifier' => 'e3b42f1d-5235-94ce-48d3-c2d09846c642',
                    'title' => 'Nobel Prize',
                    'description' => 'Not bad.'
                ],
                [
                    '@type' => 'highlight',
                    'identifier' => 'd8a77e2c-9329-49ec-84e7-a1d05946c447',
                    'title' => 'Purlitzer Prize',
                    'description' => 'The greatest author ever.'
                ]
            ],
            'books' => [
                [
                    '@type' => 'book',
                    'identifier' => '558de401-1647-4a70-a563-1e374dfcb699',
                    'referenced_identifier' => 'book1'
                ],
                [
                    '@type' => 'book',
                    'identifier' => '0e433036-09bf-4027-a709-af925db88341',
                    'referenced_identifier' => 'book2'
                ]
            ]
        ],
        'payload' => [
            'author' => [
                'identifier' => $aggregate_root_identifier,
                'firstname' => 'Amitav',
                'lastname' => 'Gosh',
                'products' => [
                    [
                        '@type' => 'highlight',
                        'identifier' => 'd8a77e2c-9329-49ec-84e7-a1d05946c447',
                        'title' => 'Wurlitzer Prize',
                        'description' => 'The greatest jukebox ever.'
                    ],
                    [
                        '@type' => 'highlight',
                        'title' => 'Tripadvisor',
                        'description' => 'Best restaurants 2020.'
                    ]
                ],
                'books' => [
                    [
                        '@type' => 'book',
                        'identifier' => '0e433036-09bf-4027-a709-af925db88341',
                        'referenced_identifier' => 'book2'
                    ],
                    [
                        '@type' => 'book',
                        'referenced_identifier' => 'book3'
                    ]
                ]
            ]
        ],
        'expected_command' => [
            '@type' => 'Honeybee\Tests\Fixture\BookSchema\Task\ModifyAuthor\ModifyAuthorCommand',
            'values' => [],
            'aggregate_root_identifier' => $aggregate_root_identifier,
            'aggregate_root_type' => 'honeybee-cmf.aggregate_fixtures.author',
            'known_revision' => 7,
            'embedded_entity_commands' => [
                [
                    '@type' => $type_namespace_prefix . 'ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand',
                    'embedded_entity_identifier' => 'd8a77e2c-9329-49ec-84e7-a1d05946c447',
                    'embedded_entity_type' => 'highlight',
                    'parent_attribute_name' => 'products',
                    'values' => [
                        'title' => 'Wurlitzer Prize',
                        'description' => 'The greatest jukebox ever.'
                    ],
                    'embedded_entity_commands' => [],
                    'position' => 0,
                    'metadata' => []
                ],
                [
                    '@type' => $type_namespace_prefix . 'RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand',
                    'embedded_entity_identifier' => 'e3b42f1d-5235-94ce-48d3-c2d09846c642',
                    'embedded_entity_type' => 'highlight',
                    'parent_attribute_name' => 'products',
                    'embedded_entity_commands' => [],
                    'metadata' => []
                ],
                [
                    '@type' => $type_namespace_prefix . 'AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'embedded_entity_type' => 'highlight',
                    'parent_attribute_name' => 'products',
                    'values' => [
                        'title' => 'Tripadvisor',
                        'description' => 'Best restaurants 2020.'
                    ],
                    'embedded_entity_commands' => [],
                    'position' => 1,
                    'metadata' => []
                ],
                [
                    '@type' => $type_namespace_prefix . 'ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand',
                    'embedded_entity_identifier' => '0e433036-09bf-4027-a709-af925db88341',
                    'embedded_entity_type' => 'book',
                    'parent_attribute_name' => 'books',
                    'values' => [],
                    'embedded_entity_commands' => [],
                    'position' => 0,
                    'metadata' => []
                ],
                [
                    '@type' => $type_namespace_prefix . 'RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand',
                    'embedded_entity_identifier' => '558de401-1647-4a70-a563-1e374dfcb699',
                    'embedded_entity_type' => 'book',
                    'parent_attribute_name' => 'books',
                    'embedded_entity_commands' => [],
                    'metadata' => []
                ],
                [
                    '@type' => $type_namespace_prefix . 'AddEmbeddedEntity\AddEmbeddedEntityCommand',
                    'embedded_entity_type' => 'book',
                    'parent_attribute_name' => 'books',
                    'values' => [
                        'referenced_identifier' => 'book3'
                    ],
                    'embedded_entity_commands' => [],
                    'position' => 1,
                    'metadata' => []
                ]
            ],
            'metadata' => []
        ]
    ],
];
