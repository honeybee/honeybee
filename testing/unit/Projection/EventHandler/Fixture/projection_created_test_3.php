<?php

/**
 * Test cases are described by related flow chart images
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
            'title' => 'R-Type'
        ],
        'aggregate_root_identifier' => 'honeybee.fixtures.game-49c5a3b7-8127-4169-8676-a9ebb5229142-de_DE-1',
        'aggregate_root_type' => 'honeybee-tests.game_schema.game',
        'embedded_entity_events' => [
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
                'embedded_entity_events' => [
                    [
                        '@type' =>
                        'Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\EmbeddedEntityModifiedEvent',
                        'data' => [
                            'identifier' => '6c469af2-f60a-4bd9-b220-822a377f033e',
                            'nickname' => 'Game Player Nickname'
                        ],
                        'position' => 1,
                        'embedded_entity_identifier' => '6c469af2-f60a-4bd9-b220-822a377f033e',
                        'embedded_entity_type' => 'profile',
                        'parent_attribute_name' => 'profiles',
                        'embedded_entity_events' => []
                    ]
                ]
            ]
        ],
        'seq_number' => 1,
        'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
        'iso_date' => '2016-04-28T10:52:37.371793+00:00',
        'metadata' => []
    ],
    'projections' => [
        [
            '@type' => 'honeybee-tests.game_schema.player::projection.standard',
            'identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'revision' => 1,
            'uuid' => 'a726301d-dbae-4fb6-91e9-a19188a17e71',
            'language' => 'de_DE',
            'version' => 1,
            'created_at' => '2016-03-27T10:52:37.371793+00:00',
            'modified_at' => '2016-03-27T10:52:37.371793+00:00',
            'workflow_state' => 'edit',
            'workflow_parameters' => [],
            'metadata' => [],
            'name' => 'Player 1, 2 Profiles',
            'location' => [ 'lon' => 1.2, 'lat' => 2.1 ],
            'profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                    'alias' => 'mockprofile1',
                    'tags' => [ 'mock', 'player', 'profile', 'one' ],
                    'badges' => [],
                    'unmirrored_badges' => [],
                    'teams' => []
                ],
                [
                    '@type' => 'profile',
                    'identifier' => '6c469af2-f60a-4bd9-b220-822a377f033e',
                    'alias' => 'mockprofile2',
                    'tags' => [],
                    'badges' => [],
                    'unmirrored_badges' => [],
                    'teams' => []
                ]
            ],
            'simple_profiles' => [
                [
                    '@type' => 'profile',
                    'identifier' => 'b46caba3-19ef-4bdf-b5ab-37f925485005',
                    'alias' => 'hiddenprofile1',
                    'tags' => [ 'hidden', 'player', 'profile', 'one' ],
                    'badges' => [],
                    'unmirrored_badges' => []
                ]
            ]
        ]
    ],
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
            'title' => 'R-Type',
            'challenges' => [],
            'players' => [
                [
                    '@type' => 'player',
                    'identifier' => 'ca8a5117-927a-4f94-8b0d-7b0be6196acf',
                    'referenced_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
                    'tagline' => '',
                    'name' => 'Player 1, 2 Profiles',
                    'area' => [ 'lon' => 1.2, 'lat' => 2.1 ],
                    'profiles' => [
                        [
                            '@type' => 'profile',
                            'identifier' => '94a03a00-8420-4ee2-a4f7-0e0ff1989592',
                            'nickname' => '',
                            'alias' => 'mockprofile1',
                            'tags' => [ 'mock', 'player', 'profile', 'one' ],
                            'badges' => [],
                            'memberships' => []
                        ],
                        [
                            '@type' => 'profile',
                            'identifier' => '6c469af2-f60a-4bd9-b220-822a377f033e',
                            'nickname' => 'Game Player Nickname',
                            'alias' => 'mockprofile2',
                            'tags' => [],
                            'badges' => [],
                            'memberships' => []
                        ]
                    ]
                ]
            ]
        ]
    ]
];
