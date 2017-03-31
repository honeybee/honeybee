<?php

/**
 * Test cases are described by related flow chart images
 */
return [
    'event_state' => [
        '@type' => 'Honeybee\Tests\Fixture\GameSchema\Task\GameModifiedEvent',
        'data' => [
            'title' => 'Doom 3'
        ],
        'aggregate_root_identifier' => 'honeybee.fixtures.game-a7cec777-d932-4bbd-8156-261138d3fe39-de_DE-1',
        'aggregate_root_type' => 'honeybee_tests.game_schema.game',
        'embedded_entity_events' => [],
        'seq_number' => 3,
        'uuid' => 'a7cec777-d932-4bbd-8156-261138d3fe39',
        'iso_date' => '2016-04-28T10:53:53.530472+00:00',
        'metadata' => []
    ],
    'subject' => [
        '@type' => 'honeybee_tests.game_schema.game::projection.standard',
        'identifier' => 'honeybee.fixtures.game-a7cec777-d932-4bbd-8156-261138d3fe39-de_DE-1',
        'revision' => 2,
        'uuid' => 'a7cec777-d932-4bbd-8156-261138d3fe39',
        'language' => 'de_DE',
        'version' => 1,
        'created_at' => '2016-04-28T10:53:53.530472+00:00',
        'modified_at' => '2016-04-28T10:53:53.530472+00:00',
        'workflow_state' => 'edit',
        'workflow_parameters' => [],
        'metadata' => [],
        'title' => 'Duke Nukem',
        'challenges' => [],
        'players' => []
    ],
    'projections' => [],
    'expectations' => [
        [
            '@type' => 'honeybee_tests.game_schema.game::projection.standard',
            'identifier' => 'honeybee.fixtures.game-a7cec777-d932-4bbd-8156-261138d3fe39-de_DE-1',
            'revision' => 3,
            'uuid' => 'a7cec777-d932-4bbd-8156-261138d3fe39',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:53:53.530472+00:00',
            'modified_at' => '2016-04-28T10:53:53.530472+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Doom 3',
            'challenges' => [],
            'players' => []
        ]
    ]
];
