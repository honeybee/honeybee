<?php
/**
 * @codeCoverageIgnore
 */
return [
    '@type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand',
    'values' => [
        'identifier' => 'honeybee-cmf.aggregate_fixtures.author-39249423-1dd3-3ded-a199-930ed6813060-de_DE-4321',
        'revision' => 1,
        'uuid' => '39249423-1dd3-3ded-a199-930ed6813060',
        'language' => 'de_DE',
        'version' => 4321,
        'firstname' => 'George',
        'lastname' => 'Clinton',
        'email' => 'funk@me.com',
        'blurb' => 'Making America funk again'
    ],
    'aggregate_root_type' => 'honeybee-cmf.aggregate_fixtures.author',
    'embedded_entity_commands' => [
        [
            '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
            'values' => [
                'title' => 'Winner',
                'description' => 'The GOAT'
            ],
            'position' => 0,
            'embedded_entity_type' => 'highlight',
            'parent_attribute_name' => 'products',
            'embedded_entity_commands' => [],
            'uuid' => $fixture_data['embedded_entity_commands'][0]['uuid'],
            'metadata' => []
        ],
        [
            '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand',
            'values' => [
                'referenced_identifier' =>
                    'honeybee-cmf.aggregate_fixtures.book-29200952-0a52-41ea-ae41-50e73b4777da-de_DE-123'
            ],
            'position' => 0,
            'embedded_entity_type' => 'book',
            'parent_attribute_name' => 'books',
            'embedded_entity_commands' => [],
            'uuid' => $fixture_data['embedded_entity_commands'][1]['uuid'],
            'metadata' => []
        ]
    ],
    'uuid' => $fixture_data['uuid'],
    'metadata' => []
];
