<?php

return [
    '@type' => 'Honeybee\Tests\Fixture\BookSchema\Model\Task\CreateAuthor\CreateAuthorCommand',
    'values' => [
        'identifier' => 'honeybee-cmf.aggregate_fixtures.author-39249423-1dd3-3ded-a199-930ed6813060-de_DE-9490',
        'revision' => 1,
        'uuid' => '39249423-1dd3-3ded-a199-930ed6813060',
        'language' => 'de_DE',
        'version' => 9490,
        'firstname' => 'Donald',
        'lastname' => 'Drumpf',
        'email' => 'win@me.com',
        'blurb' => 'Making America great again'
    ],
    'aggregate_root_type' => 'honeybee-cmf.aggregate_fixtures.author',
    'embedded_entity_commands' => [],
    'uuid' => $fixture_data['uuid'],
    'metadata' => []
];
