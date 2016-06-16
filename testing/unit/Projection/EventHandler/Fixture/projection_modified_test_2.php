<?php

/*
 *  Test cases are described by related flow chart images
 */
return [
    'event_state' => [
        '@type' => 'Honeybee\Tests\Fixture\GameSchema\Task\GameModifiedEvent',
        'data' => [
            'title' => 'Quake 9'
        ],
        'aggregate_root_identifier' => 'honeybee.fixtures.game-a7cec777-d932-4bbd-8156-261138d3fe39-de_DE-1',
        'aggregate_root_type' => 'honeybee-tests.game_schema.game',
        'embedded_entity_events' => [
            [
                '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\EmbeddedEntityRemovedEvent',
                'data' => [
                    'identifier' => '99d68357-595e-41f3-9675-b532a8ed968f',
                    'referenced_identifier' =>
                        'honeybee.fixtures.player-c9a1fd68-e6e5-462c-a544-c86f0812cf6c-de_DE-1'
                ],
                'position' => 0,
                'embedded_entity_identifier' => '99d68357-595e-41f3-9675-b532a8ed968f',
                'embedded_entity_type' => 'player',
                'parent_attribute_name' => 'players',
                'embedded_entity_events' => []
            ],
            [
                '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\EmbeddedEntityAddedEvent',
                'data' => [
                    'identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                    'referenced_identifier' =>
                        'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1'
                ],
                'position' => 0,
                'embedded_entity_identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                'embedded_entity_type' => 'player',
                'parent_attribute_name' => 'players',
                'embedded_entity_events' => []
            ],
            [
                '@type' => 'Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\EmbeddedEntityAddedEvent',
                'data' => [
                    'identifier' => '5cf33cf1-554b-40be-98e7-ef7b4e98ec8c',
                    'attempts' => 5
                ],
                'position' => 1,
                'embedded_entity_identifier' => '5cf33cf1-554b-40be-98e7-ef7b4e98ec8c',
                'embedded_entity_type' => 'challenge',
                'parent_attribute_name' => 'challenges',
                'embedded_entity_events' => []
            ]
        ],
        'seq_number' => 3,
        'uuid' => 'a7cec777-d932-4bbd-8156-261138d3fe39',
        'iso_date' => '2016-04-28T10:53:53.530472+00:00',
        'metadata' => []
    ],
    'subject' => [
        '@type' => 'honeybee-tests.game_schema.game',
        'identifier' => 'honeybee.fixtures.game-a7cec777-d932-4bbd-8156-261138d3fe39-de_DE-1',
        'revision' => 2,
        'uuid' => 'a7cec777-d932-4bbd-8156-261138d3fe39',
        'short_id' => 0,
        'language' => 'de_DE',
        'version' => 1,
        'created_at' => '2016-04-28T10:51:53.530472+00:00',
        'modified_at' => '2016-04-28T10:51:53.530472+00:00',
        'workflow_state' => 'edit',
        'workflow_parameters' => [],
        'metadata' => [],
        'title' => 'Doom 3',
        'challenges' => [
            [
                '@type' => 'challenge',
                'identifier' => 'f1e876f5-7648-42ea-b813-a6c4bd3c8e3f',
                'attempts' => 0
            ]
        ],
        'players' => [
            [
                '@type' => 'player',
                'identifier' => '99d68357-595e-41f3-9675-b532a8ed968f',
                'referenced_identifier' => 'honeybee.fixtures.player-c9a1fd68-e6e5-462c-a544-c86f0812cf6c-de_DE-1',
                'tagline' => '',
                'name' => 'Player to be removed',
                'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                'profiles' => []
            ]
        ]
    ],
    'projections' => [
        [
            '@type' => 'honeybee-tests.game_schema.player',
            'identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 1,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:52:37.371793+00:00',
            'modified_at' => '2016-03-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'name' => 'Player 1, No Profiles',
            'location' => [ 'lon' => 2.313, 'lat' => 2.09 ],
            'profiles' => [],
            'simple_profiles' => []
        ]
    ],
    'expected' => [
        [
            '@type' => 'honeybee-tests.game_schema.game',
            'identifier' => 'honeybee.fixtures.game-a7cec777-d932-4bbd-8156-261138d3fe39-de_DE-1',
            'revision' => 3,
            'uuid' => 'a7cec777-d932-4bbd-8156-261138d3fe39',
            'short_id' => 0,
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-04-28T10:51:53.530472+00:00',
            'modified_at' => '2016-04-28T10:53:53.530472+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'title' => 'Quake 9',
            'challenges' => [
                [
                    '@type' => 'challenge',
                    'identifier' => 'f1e876f5-7648-42ea-b813-a6c4bd3c8e3f',
                    'attempts' => 0
                ],
                [
                    '@type' => 'challenge',
                    'identifier' => '5cf33cf1-554b-40be-98e7-ef7b4e98ec8c',
                    'attempts' => 5
                ]
            ],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                    'referenced_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Player 1, No Profiles',
                    'area' => [ 'lon' => 2.313, 'lat' => 2.09 ],
                    'profiles' => []
                ]
            ]
        ]
    ]
];
