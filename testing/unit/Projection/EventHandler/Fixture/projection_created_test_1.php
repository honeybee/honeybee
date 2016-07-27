<?php

/**
 * Test cases are described by related flow chart images
 * @codeCoverageIgnore
 */
return [
    'event_state' => [
        '@type' => 'Honeybee\Tests\Fixture\GameSchema\Task\GameCreatedEvent',
        'data' => [
            'identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'workflow_state' => 'edit',
            'title' => 'Quake 9'
        ],
        'aggregate_root_identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
        'aggregate_root_type' => 'honeybee-tests.game_schema.game',
        'embedded_entity_events' => [],
        'seq_number' => 1,
        'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
        'iso_date' => '2016-04-28T10:52:37.371793+00:00',
        'metadata' => []
    ],
    'projections' => [],
    'expectations' => [
        [
            '@type' => 'honeybee-tests.game_schema.game::projection.standard',
            'identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
            'revision' => 1,
            'uuid' => '49c5a3b7-8127-4169-8676-a9ebb5229142',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:52:37.371793+00:00',
            'modified_at' => '2016-04-28T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Quake 9',
            'challenges' => [],
            'players' => []
        ]
    ]
];
